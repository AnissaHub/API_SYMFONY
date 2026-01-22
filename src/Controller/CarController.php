<?php

namespace App\Controller;

use App\Service\CarService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class CarController extends AbstractController
{
    public function __construct(private CarService $carService) {}

   #[Route('/api/cars', methods: ['GET'])]
public function index(Request $request): JsonResponse
{
    $marque = $request->query->get('marque');
    $etat   = $request->query->get('etat');


    $validEtats = ['pris_en_charge','diagnostic','attente_pieces','en_reparation','pret','livre'];

    // Si un état est fourni, vérifier qu'il est valide
    if ($etat && !in_array($etat, $validEtats)) {             // si L’état fourni n’est PAS dans la liste des états autorisés
        return $this->json([
            'error' => "État invalide. Choisir parmi : ".implode(', ', $validEtats)
        ], 400);
    }

    // Choix du filtre 
    if ($etat) {
        $cars = $this->carService->getCarsByEtat($etat);
        if (empty($cars)) {
            return $this->json([
                'error' => "Aucune voiture trouvée pour l'état '$etat'."
            ], 404);
        }
    } elseif ($marque) {
        $cars = $this->carService->getCarsByMarque($marque);
        if (empty($cars)) {
            return $this->json([
                'error' => "Aucune voiture trouvée pour la marque '$marque'."
            ], 404);
        }
    
    
    } else {
        // Pas de filtre → toutes les voitures
        $cars = $this->carService->getAllCars();
    }

    return $this->json(['data' => $cars], 200);
}


    #[Route('/api/cars/{immatriculation}', name: 'api_car_detail', methods: ['GET'])]
    public function show(string $immatriculation): JsonResponse
    {
        $car = $this->carService->getCarByImmatriculation($immatriculation);

        if (!$car) {
            return $this->json(['error' => 'Voiture non trouvée'], 404);
        }

        return $this->json($car);
    }
    #[Route('/api/cars', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);       // récupérer le corps de la requete
        // Vérifier si la voiture existe déjà
    $existingCar = $this->carService->getCarByImmatriculation($data['immatriculation']);
    if ($existingCar) {
    return $this->json(['error' => 'Cette immatriculation existe déjà'], 400);
}

        $car = $this->carService->createCar($data);
        $errors = $validator->validate($car);

    if (count($errors) > 0) {

        return $this->json(['errors' => (string) $errors], 400);   // (string) $errors transforme la liste en texte lisible.
    }
        $em->persist($car); // Doctrine prépare une requête SQL INSERT
        $em->flush();
        return $this->json([
       'message' => 'Voiture créée',
       'car' => $this->carService->formatCar($car),], 201);
    }

    #[Route('/api/cars/{immatriculation}', methods: ['PUT'])]
    public function update(string $immatriculation, Request $request, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $car = $this->carService->getCarByImmatriculationEntity($immatriculation);

        if (!$car) {
            return $this->json(['message' => 'Voiture non trouvée'], 404);
        }
        
        $this->carService->updateCar($car, $data);
        $errors = $validator->validate($car);
         if (count($errors) > 0) {

        return $this->json(['errors' => (string) $errors], 400);   // (string) $errors transforme la liste en texte lisible.
     }

        $em->flush();

        return $this->json([
            'message' => 'Voiture mise à jour avec succès',
            'car' => $this->carService->formatCar($car)     // Retourne l’objet formaté et un message de succès.
        ], 200);
     }
     #[Route('/api/cars/{immatriculation}/etat', methods: ['PATCH'])]
     public function updateEtat(string $immatriculation, Request $request, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
     {
        $data = json_decode($request->getContent(), true);

        $car = $this->carService->getCarByImmatriculationEntity($immatriculation);
        if (!$car) {
            return $this->json(['message' => 'Voiture non trouvée'], 404);
        }

        $this->carService->updateEtat($car, $data);
          $errors = $validator->validate($car);
         if (count($errors) > 0) {

        return $this->json(['errors' => (string) $errors], 400);   // (string) $errors transforme la liste en texte lisible.
     }

        $em->flush();
        return $this->json([
            'message' => 'Etat mis à jour avec succès',
            'car' => $this->carService->formatCar($car)
        ], 200);
     }

     /*On utilise formatCar() après la modification pour transformer l’objet Car en tableau JSON.
     Cela permet d’afficher correctement toutes les informations, notamment les dates, même si on a modifié seulement l’état */

     #[Route('/api/cars/{immatriculation}', methods: ['DELETE'])]
     public function delete(string $immatriculation, EntityManagerInterface $em): JsonResponse
     {
        $car = $this->carService->getCarByImmatriculationEntity($immatriculation);

        if (!$car) {
            return $this->json(['message' => 'Voiture non trouvée'], 404);
        }

        $em->remove($car);       // Doctrine prépare la suppression
        $em->flush();

        return $this->json(['message' => 'Voiture supprimée avec succès'], 200);
     }
}
