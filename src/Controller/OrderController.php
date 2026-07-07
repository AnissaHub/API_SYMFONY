<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Repository\CarRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class OrderController extends AbstractController
{
    #[Route('/api/orders', methods: ['POST'])]
    public function createOrder(
        Request $request,
        EntityManagerInterface $em,
        CarRepository $carRepository
    ): JsonResponse {

        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Non connecté'], 401);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['total'], $data['cars'])) {
            return $this->json(['error' => 'Payload invalide'], 400);
        }

        $total = (float) $data['total'];
        $cars = $data['cars'];

        if ($total <= 0) {
            return $this->json(['error' => 'Total invalide'], 400);
        }

        //  création commande
        $commande = new Commande();
        $commande->setTotal($total);
        $commande->setStatut('payée');
        $commande->setCreatedAt(new \DateTimeImmutable());

        $em->persist($commande);

        //  validation voitures
        foreach ($cars as $immatriculation) {

            $car = $carRepository->findOneBy([
                'immatriculation' => $immatriculation
            ]);

            if (!$car) {
                return $this->json([
                    'error' => "Voiture introuvable : $immatriculation"
                ], 404);
            }
        }

        try {
            $em->flush();
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur base de données',
                'details' => $e->getMessage()
            ], 500);
        }

        return $this->json([
            'message' => 'Commande créée',
            'id' => $commande->getId()
        ]);
    }
}
