<?php

namespace App\Controller;

use App\Repository\UtilisateurRepository;
use App\Service\CarService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class CarController extends AbstractController
{
    public function __construct(
        private CarService $carService,
        private UtilisateurRepository $utilisateurRepository
    ) {}

    // ── LISTE — public ───────────────────────────────
    #[Route('/api/cars', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $cars = $this->carService->getAllCars();
        return $this->json(['data' => $cars]);
    }

    // ── DÉTAIL — public ──────────────────────────────
    #[Route('/api/cars/{immatriculation}', methods: ['GET'])]
    public function show(string $immatriculation): JsonResponse
    {
        $car = $this->carService->getCarByImmatriculationEntity($immatriculation);
        if (!$car) {
            return $this->json(['error' => 'Voiture non trouvée'], 404);
        }

        return $this->json([
            'data' => $this->carService->formatCar($car)
        ]);
    }

    // ── CRÉATION — admin uniquement ──────────────────
    #[Route('/api/cars', name: 'api_car_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['message' => 'Accès interdit'], 403);
        }

        $data = json_decode($request->getContent(), true);

        $existingCar = $this->carService->getCarByImmatriculation($data['immatriculation']);
        if ($existingCar) {
            return $this->json(['error' => 'Cette immatriculation existe déjà'], 400);
        }

        $car = $this->carService->createCar($data);

        $errors = $validator->validate($car);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], 400);
        }

        $em->persist($car);
        $em->flush();

        return $this->json(['message' => 'Voiture créée'], 201);
    }

    // ── MODIFICATION — admin uniquement ─────────────
    #[Route('/api/cars/{immatriculation}', methods: ['PUT'])]
    public function update(string $immatriculation, Request $request, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['message' => 'Accès interdit'], 403);
        }

        $data = json_decode($request->getContent(), true);

        $car = $this->carService->getCarByImmatriculationEntity($immatriculation);
        if (!$car) {
            return $this->json(['message' => 'Voiture non trouvée'], 404);
        }

        $this->carService->updateCar($car, $data);

        $errors = $validator->validate($car);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], 400);
        }

        $em->flush();

        return $this->json([
            'message' => 'Voiture mise à jour avec succès',
            'car' => $this->carService->formatCar($car)
        ], 200);
    }

    // ── PATCH ÉTAT — admin uniquement ────────────────
    #[Route('/api/cars/{immatriculation}/etat', methods: ['PATCH'])]
    public function updateEtat(string $immatriculation, Request $request, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['message' => 'Accès interdit'], 403);
        }

        $data = json_decode($request->getContent(), true);

        $car = $this->carService->getCarByImmatriculationEntity($immatriculation);
        if (!$car) {
            return $this->json(['message' => 'Voiture non trouvée'], 404);
        }

        $this->carService->updateEtat($car, $data);

        $errors = $validator->validate($car);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], 400);
        }

        $em->flush();

        return $this->json([
            'message' => 'Etat mis à jour avec succès',
            'car' => $this->carService->formatCar($car)
        ], 200);
    }

    // ── SUPPRESSION — admin uniquement ───────────────
    #[Route('/api/cars/{immatriculation}', methods: ['DELETE'])]
    public function delete(string $immatriculation, EntityManagerInterface $em): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['message' => 'Accès interdit'], 403);
        }

        $car = $this->carService->getCarByImmatriculationEntity(trim($immatriculation));
        if (!$car) {
            return $this->json(['message' => 'Voiture non trouvée'], 404);
        }

        $em->remove($car);
        $em->flush();

        return $this->json(['message' => 'Voiture supprimée avec succès'], 200);
    }
}
