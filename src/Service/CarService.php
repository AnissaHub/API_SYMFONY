<?php

namespace App\Service;

use App\Entity\Utilisateur;
use App\Entity\Car;
use App\Repository\CarRepository;
use App\Repository\UtilisateurRepository;

class CarService
{
    public function __construct(
        private CarRepository $carRepository,
        private UtilisateurRepository $utilisateurRepository
    ) {}

    //LISTE
    //Retourne toutes les voitures appartenant à un utilisateur
   public function getCarsForUser(Utilisateur $user): array
{
    return array_map(                     //array_map Cette fonction applique une transformation à chaque élément du tableau.
        [$this, 'formatCar'],             // appelle la méthode formatCar() pour chaque voiture
        $this->carRepository->findBy(['utilisateur' => $user])
    );
}



    public function getAllCars(): array
    {
        return array_map([$this, 'formatCar'], $this->carRepository->findAll()); //Applique formatCar() à chaque objet pour transformer en tableau.

    }

    public function getCarsByMarque(string $marque): array
    {
        return array_map(
            [$this, 'formatCar'],
            $this->carRepository->findBy(['marque' => $marque])
        );
    }

    public function getCarsByEtat(string $etat): array
    {
        return array_map(
            [$this, 'formatCar'],
            $this->carRepository->findBy(['etat' => $etat])
        );
    }

    //DÉTAIL

    public function getCarByImmatriculation(string $immatriculation): ?array
    {
        $car = $this->carRepository->findOneBy(['immatriculation' => $immatriculation]);
        return $car ? $this->formatCar($car) : null;
    }

    public function getCarByImmatriculationEntity(string $immatriculation): ?Car
    {
        return $this->carRepository->findOneBy(['immatriculation' => $immatriculation]);
    }

     public function getCarByImmatriculationForUser(
     string $immatriculation, Utilisateur $user): ?Car
     {
         return $this->carRepository->findOneBy([
        'immatriculation' => $immatriculation,
        'utilisateur' => $user
       ]);
    }


      // CRÉATION
  

    public function createCar(array $data): Car
    {
        if (!isset($data['utilisateur_id'])) {
            throw new \InvalidArgumentException('utilisateur_id manquant');
        }

        $utilisateur = $this->utilisateurRepository->find($data['utilisateur_id']);
        if (!$utilisateur) {
            throw new \InvalidArgumentException('Utilisateur inexistant');
        }

        $car = new Car();
        $car->setImmatriculation($data['immatriculation']);
        $car->setMarque($data['marque']);
        $car->setModele($data['modele']);
        $car->setEtat($data['etat']);
        $car->setUtilisateur($utilisateur);
        $car->setAnnee($data['annee'] ?? null);
        $car->setCouleur($data['couleur'] ?? null);
        $car->setObservations($data['observations'] ?? null);

        // Dates sécurisées
        $car->setDateEntree(
            isset($data['dateEntree'])
                ? new \DateTime($data['dateEntree'])
                : new \DateTime()
        );

        if (!empty($data['dateSortie'])) {
            $car->setDateSortie(new \DateTime($data['dateSortie']));
        } else {
            $car->setDateSortie(null);
        }
        return $car;
    }

   
       //MISE À JOUR
   

    public function updateCar(Car $car, array $data): void
    {
        if (isset($data['immatriculation'])) {
            $car->setImmatriculation($data['immatriculation']);
        }
        if (isset($data['marque'])) {
            $car->setMarque($data['marque']);
        }
        if (isset($data['modele'])) {
            $car->setModele($data['modele']);
        }
        if (isset($data['etat'])) {
            $car->setEtat($data['etat']);
        }

        $car->setAnnee($data['annee'] ?? $car->getAnnee());
        $car->setCouleur($data['couleur'] ?? $car->getCouleur());
        $car->setObservations($data['observations'] ?? $car->getObservations());

        if (isset($data['dateEntree'])) {
            $car->setDateEntree(new \DateTime($data['dateEntree']));
        }

        if (array_key_exists('dateSortie', $data)) {
            $car->setDateSortie(
                $data['dateSortie']
                    ? new \DateTime($data['dateSortie'])
                    : null
            );
        }

        if (isset($data['utilisateur_id'])) {
            $utilisateur = $this->utilisateurRepository->find($data['utilisateur_id']);
            if ($utilisateur) {
                $car->setUtilisateur($utilisateur);
            }
        }
    }

    
       //PATCH ÉTAT
    

    public function updateEtat(Car $car, array $data): void
    {
        if (!isset($data['etat'])) {
            throw new \InvalidArgumentException('État manquant');
        }

        $car->setEtat($data['etat']);
    }


       //FORMAT JSON
    

    public function formatCar(Car $car): array
    {

        return [
            'immatriculation' => $car->getImmatriculation(),
            'marque'          => $car->getMarque(),
            'modele'          => $car->getModele(),
            'annee'           => $car->getAnnee(),
            'couleur'         => $car->getCouleur(),
            'etat'            => $car->getEtat(),
            'dateEntree'      => $car->getDateEntree()?->format('Y-m-d H:i:s'),
            'dateSortie'      => $car->getDateSortie()?->format('Y-m-d H:i:s'),
            'observations'    => $car->getObservations(),
            'utilisateur_id'  => $car->getUtilisateur()?->getId(),
        ];
    }
}²²

