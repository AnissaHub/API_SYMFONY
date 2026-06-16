<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\CommandeItem;
use App\Repository\CarRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class CommandeController extends AbstractController
{
    // ── Créer une commande ────────────────────────────
    #[Route('/api/commandes', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        CarRepository $carRepository
    ): JsonResponse {

        // Vérifier que l'utilisateur est connecté
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Non connecté'], 401);
        }

        $data = json_decode($request->getContent(), true);

        // Vérifier que les items sont présents
        if (empty($data['items'])) {
            return $this->json(['error' => 'Panier vide'], 400);
        }

        // Créer la commande
        $commande = new Commande();
        $commande->setUtilisateur($user);
        $commande->setTotal($data['total']);
        $commande->setStatut('en_attente');

        // Ajouter les items
        foreach ($data['items'] as $itemData) {
            $car = $carRepository->findOneBy(['immatriculation' => $itemData['immatriculation']]);

            if (!$car) continue;

            $item = new CommandeItem();
            $item->setCar($car);
            $item->setQuantite($itemData['quantite']);
            $item->setPrix($itemData['prix']);
            $item->setCommande($commande);

            $em->persist($item);
            $commande->addItem($item);
        }

        $em->persist($commande);
        $em->flush();

        return $this->json([
            'message' => 'Commande créée ✅',
            'commande_id' => $commande->getId()
        ], 201);
    }

    // ── Liste des commandes de l'utilisateur ──────────
    #[Route('/api/commandes', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Non connecté'], 401);
        }

        $commandes = $user->getCommandes();

        $data = array_map(fn($c) => [
            'id'         => $c->getId(),
            'total'      => $c->getTotal(),
            'statut'     => $c->getStatut(),
            'createdAt'  => $c->getCreatedAt()->format('d/m/Y H:i'),
            'items'      => array_map(fn($i) => [
                'immatriculation' => $i->getCar()->getImmatriculation(),
                'marque'          => $i->getCar()->getMarque(),
                'modele'          => $i->getCar()->getModele(),
                'quantite'        => $i->getQuantite(),
                'prix'            => $i->getPrix(),
            ], $c->getItems()->toArray())
        ], $commandes->toArray());

        return $this->json(['data' => $data]);
    }
}
