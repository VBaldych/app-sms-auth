<?php

declare(strict_types=1);

namespace App\Controller;

use App\Request\SmsRequest;
use App\Service\SmsProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AuthController extends AbstractController
{
    #[Route(path: '/auth', name: 'app_auth')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Get the login error if there is one.
        $error = $authenticationUtils->getLastAuthenticationError();

        // Last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('authentication/auth.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @throws \JsonException
     */
    #[Route('/api/auth/send-sms', name: 'app_auth_sms_send', defaults: ['_rate_limit' => true], methods: 'POST')]
    public function sendSms(Request $request, SmsProvider $smsProvider, SmsRequest $smsRequest): JsonResponse
    {
        $data = $smsProvider->sendSmsCode(
            json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR)
        );

        return $data !== [] ? $this->json(
            [
                'status' => 'Success',
                'code' => Response::HTTP_OK,
                'data' => $data,
            ]
        ) : $this->json(
            [
                'status' => 'No output data',
                'code' => Response::HTTP_UNPROCESSABLE_ENTITY
            ]
        );
    }
}
