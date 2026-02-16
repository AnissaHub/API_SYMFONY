<?php

namespace App\Controller;

use App\Repository\CarRepository;
use App\Repository\UtilisateurRepository;
use App\Service\CarService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]

final class CarController extends AbstractController
{
    public function __construct(
    private CarService $carService,
    private CarRepository $carRepository,
    private UtilisateurRepository $utilisateurRepository
) {}

 //liste
   #[Route('/api/cars', methods: ['GET'])]
   public function index(): JsonResponse
 {
   
    $user = $this->getUser();   
   

  /*On récupère l’utilisateur actuellement connecté.
  $this : l’objet courant (souvent un contrôleur Symfony)
   getUser() : méthode Symfony qui retourne l’utilisateur authentifié*/


 if (in_array('ROLE_ADMIN', $user->getRoles())) {
    $cars = $this->carService->getAllCars();
    } else {
    $cars = $this->carService->getCarsForUser($user);
   }
   
        return $this->json(['data' => $cars]);
 }

 // détail
 #[Route('/api/cars/{immatriculation}', methods: ['GET'])]
 public function show(string $immatriculation): JsonResponse
 {
    $user = $this->getUser();

    if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
        // Admin : voit toutes les voitures
        $car = $this->carService->getCarByImmatriculationEntity($immatriculation);
    } else {
        // User : voit uniquement ses voitures
        $car = $this->carService->getCarByImmatriculationForUser(
            $immatriculation,
            $user
        );
    }

    if (!$car) {
        return $this->json(['error' => 'Voiture non trouvée'], 404);
    }

    return $this->json(
        $this->carService->formatCar($car)
    );
 }


  //Création
   
  #[Route('/api/cars', name: 'api_car_create', methods: ['POST'])]
public function create(Request $request, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
{
    /** @var \App\Entity\Utilisateur $user */
    $user = $this->getUser();

    $data = json_decode($request->getContent(), true);

    $existingCar = $this->carService->getCarByImmatriculation($data['immatriculation']);
    if ($existingCar) {
        return $this->json(['error' => 'Cette immatriculation existe déjà'], 400);
    }

    $owner = $user;

    if (isset($data['utilisateur_id'])) {

        if (
            !$this->isGranted('ROLE_ADMIN') &&
            $data['utilisateur_id'] != $user->getId()
        ) {
            return $this->json(['message' => 'Accès interdit'], 403);
        }

        if ($this->isGranted('ROLE_ADMIN')) {
            $owner = $this->utilisateurRepository->find($data['utilisateur_id']);

            if (!$owner) {
                return $this->json(['message' => 'Utilisateur non trouvé'], 404);
            }
        }
    }

    //  On force l'utilisateur final
    $data['utilisateur_id'] = $owner->getId();

    //  Création via le service
    $car = $this->carService->createCar($data);

    //Validation
    $errors = $validator->validate($car);
    if (count($errors) > 0) {
        return $this->json(['errors' => (string) $errors], 400);
    }
    $em->persist($car);
    $em->flush();

    return $this->json(['message' => 'Voiture créée'], 201);
}
    //modification
   #[Route('/api/cars/{immatriculation}', methods: ['PUT'])]
    public function update(string $immatriculation, Request $request, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        
        /** @var \App\Entity\Utilisateur $user */
         $user = $this->getUser();
        $data = json_decode($request->getContent(), true);

        $car = $this->carService->getCarByImmatriculationEntity($immatriculation);

        if (!$car) {
            return $this->json(['message' => 'Voiture non trouvée'], 404);
        }
        if (
            !$this->isGranted('ROLE_ADMIN') &&
            $data['utilisateur_id'] != $user->getId()
        ) {
            return $this->json(['message' => 'Accès interdit'], 403);
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
          /** @var \App\Entity\Utilisateur $user */
         $user = $this->getUser();
        $data = json_decode($request->getContent(), true);

        $car = $this->carService->getCarByImmatriculationEntity($immatriculation);
        if (!$car) {
            return $this->json(['message' => 'Voiture non trouvée'], 404);
        }
         if (
      !in_array('ROLE_ADMIN', $user->getRoles()) &&
      $car->getUtilisateur()->getId() !== $user->getId()
      ) {
         return $this->json(['message' => 'Accès interdit'], 403);
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
    /** @var \App\Entity\Utilisateur $user */
    $user = $this->getUser();

    $immatriculation = trim($immatriculation); // nettoyage
    $car = $this->carService->getCarByImmatriculationEntity($immatriculation);

    if (!$car) {
        return $this->json(['message' => 'Voiture non trouvée'], 404);
    }

    if (!$this->isGranted('ROLE_ADMIN') && $car->getUtilisateur() !== $user) {
        return $this->json(['message' => 'Accès interdit'], 403);
    }

    $em->remove($car);
    $em->flush();

    return $this->json(['message' => 'Voiture supprimée avec succès'], 200);
}

}