<?php

namespace App\Controller;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class PaymentController extends AbstractController
{
    // Créer un PaymentIntent 
    #[Route('/api/payment/create-intent', methods: ['POST'])]
    public function createIntent(Request $request): JsonResponse
    {
        // Vérifier que l'utilisateur est connecté
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Non connecté'], 401);
        }

        $data = json_decode($request->getContent(), true);
        $amount = $data['amount'] ?? 0;

        if ($amount <= 0) {
            return $this->json(['error' => 'Montant invalide'], 400);
        }

        // Initialiser Stripe avec la clé secrète
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        // Créer le PaymentIntent
        $paymentIntent = PaymentIntent::create([
            'amount'   => $amount * 100, // Stripe utilise les centimes
            'currency' => 'eur',
            'metadata' => [
                'user_id' => $user->getUserIdentifier()
            ]
        ]);

        return $this->json([
            'clientSecret' => $paymentIntent->client_secret
        ]);
    }
}
