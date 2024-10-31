<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\RateLimiter\RateLimiterFactory;

#[AsEventListener(event: 'kernel.controller', method: 'onKernelController')]
class RateLimitListener
{
    public function __construct(
        private readonly RateLimiterFactory $rateLimiterFactory
    ) { }

    /**
     * Limit the number of requests from one IP to a single route.
     */
    public function onKernelController(ControllerEvent $event): void
    {
        $request = $event->getRequest();
        $phoneNumber = $request->request->get('phone');
        $routeName = $request->attributes->get('_route');

        if ($request->attributes->get('_rate_limit')) {
            $ipLimiter = $this->rateLimiterFactory->create($routeName . '_' . $request->getClientIp());
            $phoneLimiter = $this->rateLimiterFactory->create($routeName . '_' . $phoneNumber);

            if (!$ipLimiter->consume()->isAccepted() || !$phoneLimiter->consume()->isAccepted()) {
                $event->setController(fn(): JsonResponse => new JsonResponse([
                    'error' => 'You can send SMS once per 3 minutes.'
                ], Response::HTTP_TOO_MANY_REQUESTS));
            }
        }
    }
}
