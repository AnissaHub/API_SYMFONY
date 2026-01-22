<?php

namespace App\Service;

use App\Entity\Car;
use App\Repository\CarRepository;
use App\Repository\UtilisateurRepository;

class CarService
{
  public function __construct(
    private CarRepository $carRepository,private UtilisateurRepository $utilisateurRepository) {}

    // Retourne TOUTES les voitures (GET /api/cars)
   

   public function getAllCars(): array
    {
        $cars = $this->carRepository->findAll();
        $result = [];

        foreach ($cars as $car) {
            $result[] = $this->formatCar($car);     // transforme chaque Car avec formatCar()
        }

        return $result;
    }
    public function getCarsByMarque(string $marque): array
    {
    $cars = $this->carRepository->findBy(['marque' => $marque]);
    $result = [];
    foreach ($cars as $car) {
        $result[] = $this->formatCar($car);
    }
    return $result;
    }

    public function getCarsByEtat(string $etat): array
    {
      $cars = $this->carRepository->findBy(['etat' => $etat]);
      $result = [];
      foreach ($cars as $car) {
      $result[] = $this->formatCar($car);
      }
      return $result;

    }
   
    // Retourne UNE voiture par immatriculation (GET /api/cars/{immatriculation})

    public function getCarByImmatriculation(string $immatriculation): ?array
    {
        $car = $this->carRepository->findOneBy(['immatriculation' => $immatriculation]);


        if (!$car) {
            return null;
        }

        return $this->formatCar($car);
    }
    // Créer une nouvelle voiture
    public function createCar(array $data): ?Car
{
    $car = new Car();

    $car->setImmatriculation($data['immatriculation']);
    $car->setMarque($data['marque']);
    $car->setModele($data['modele']);
    $car->setAnnee($data['annee'] ?? null);
    $car->setCouleur($data['couleur'] ?? null);
    $car->setEtat($data['etat']);
    $car->setDateEntree(new \DateTime($data['dateEntree']));

    if (!empty($data['dateSortie'])) {
        $car->setDateSortie(new \DateTime($data['dateSortie']));
    } else {
        $car->setDateSortie(null);
    }

    $car->setObservations($data['observations'] ?? null);

    //  Gestion utilisateur
    if (!isset($data['utilisateur_id'])) {
        return null; // pas d'utilisateur_id fourni
    }

    $utilisateur = $this->utilisateurRepository->find($data['utilisateur_id']);
    if (!$utilisateur) {
        return null; // utilisateur inexistant
    }

    $car->setUtilisateur($utilisateur);

    return $car;
}

    
       /*Vérifie que la clé dateSortie existe dans le tableau et la valeur n'est pas nulle, si oui Conversion de la string en objet DateTime
        sinon La date de sortie reste vide en base (NULL)*/
       
        
        
    

    // récupérer une voiture par immatriculation
    public function getCarByImmatriculationEntity(string $immatriculation): ?Car
    {
        return $this->carRepository->findOneBy(['immatriculation' => $immatriculation]);
    }
    // modifier une voiture existante
    public function updateCar(Car $car, array $data): void
    {
        $car->setImmatriculation($data['immatriculation']);
        $car->setMarque($data['marque']);
        $car->setModele($data['modele']);
        $car->setAnnee($data['annee']);
        $car->setCouleur($data['couleur']);
        $car->setEtat($data['etat']);
        $car->setDateEntree(new \DateTime($data['dateEntree']));
        $car->setDateSortie(new \DateTime($data['dateSortie']));
        $car->setObservations($data['observations']);
        // Mise à jour utilisateur
        if (isset($data['utilisateur_id'])) {
            $utilisateur = $this->utilisateurRepository->find($data['utilisateur_id']);
            if ($utilisateur) {
                $car->setUtilisateur($utilisateur);
            }
        }
    }


    // modifier l'état d'une voiture
    public function updateEtat(Car $car, array $data): void
    {
        $car->setEtat($data['etat']);
    }

    // Méthode pour transformer l'entité Car en tableau JSON

    public function formatCar(Car $car): array
    {
        return [
            'immatriculation' => $car->getImmatriculation(),
            'marque' => $car->getMarque(),
            'modele' => $car->getModele(),
            'annee' => $car->getAnnee(),
            'couleur' => $car->getCouleur(),
            'etat' => $car->getEtat(),
            'dateEntree' => $car->getDateEntree()?->format('Y-m-d H:i:s'), //si getDateEntree() n’est pas null→ on fait format()
            'dateSortie' => $car->getDateSortie()?->format('Y-m-d H:i:s'),
            'observations' => $car->getObservations(),
            'utilisateur_id' => $car->getUtilisateur()?->getId(),
        ];
    }
}