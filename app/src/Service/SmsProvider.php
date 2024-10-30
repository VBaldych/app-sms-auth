<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class SmsProvider
{
    private const REQUESTS_LIMIT = 3;
    // The code are available for 3 minutes.
    private const CODE_TIMELIFE = 3;
    // The code cache are available for 3 minutes.
    public const CACHE_TIMELIFE = 60 * self::CODE_TIMELIFE;
    // Block SMS request for 6 hours.
    public const BREAK_TIME = 6 * 60 * 60;
    public const CACHE_PREFIX = 'sms_code:';

    public function __construct(
        private readonly RedisProvider $redisProvider,
        private readonly EntityManagerInterface $entityManager
    ) { }

    /*
     * The mock implementation of sending SMS via provider.
     */
    public function sendSmsCode(array $requestData): array
    {
        $phoneNumber = $requestData['phone'];
        $user = $this->entityManager->getRepository(User::class)->findOrCreateUserByPhone($phoneNumber);
        // Generate a code.
        $code = random_int(000000, 999999);
        $this->cacheCode($user, $code);

        return ['sms_code' => $code];
    }

    private function cacheCode(User $user, int $code): ?JsonResponse
    {
        $cacheKey = self::CACHE_PREFIX . $user->getPhone();
        $cacheData = $this->redisProvider->get($cacheKey);

        if (
            $cacheData
            && $cacheData['count'] >= self::REQUESTS_LIMIT
        ) {
            // Block SMS request for 6 hours.
            $this->redisProvider->set($cacheKey, json_encode($cacheData, JSON_THROW_ON_ERROR), self::BREAK_TIME);

            return new JsonResponse(
                [
                    'error' => 'You have reached the SMS sending limit. Please try again after 6 hours.'
                ],
                JsonResponse::HTTP_TOO_MANY_REQUESTS
            );
        }

        $newData = [
            'user' => $user->getUserIdentifier(),
            'code' => $code,
            'count' => $cacheData ? $cacheData['count'] + 1 : 1,
        ];

        $this->redisProvider->set(
            $cacheKey,
            json_encode($newData, JSON_THROW_ON_ERROR),
            self::CACHE_TIMELIFE
        );

        return null;
    }
}