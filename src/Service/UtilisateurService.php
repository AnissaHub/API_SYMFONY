<?php

namespace App\Service;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UtilisateurService
{
    public function __construct(
        private UtilisateurRepository $utilisateurRepository,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    // Vérifie si un email existe déjà ()
     
    public function emailExiste(string $email): bool
    {
        return null !== $this->utilisateurRepository->findOneBy(['email' => $email]);
    }

    // Création d'un utilisateur (ENTITÉ)
    
    public function createUtilisateur(array $data): Utilisateur
    {
        $user = new Utilisateur();
        $user->setEmail($data['email']);
        $user->setRoles($data['roles'] ?? ['ROLE_USER']);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password'] );

        $user->setPassword($hashedPassword);

        return $user;
    }
    // afficher tous les users
    public function getAllUsers(): array
{
    $users = $this->utilisateurRepository->findAll();
    $data = [];

    foreach ($users as $user) {
        $data[] = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
        ];
    }

    return $data;
}

    public function getUtilisateurById(int $id): ?Utilisateur
    {
        return $this->utilisateurRepository->find($id);
    }

    public function updateUserRoles(int $id, array $roles): void
{
    $user = $this->utilisateurRepository->find($id);
    $user->setRoles($roles);
}
    // mise à jour 
   public function updateProfile(Utilisateur $user, array $data): ?Utilisateur
   {
    if (isset($data['email'])){
        $user->setEmail($data['email']);
    }
    if (isset($data['password']))
      {
        $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($data['password']);
      }
      $user->setPassword($hashedPassword);
       return $user;
   }


    //À UTILISER POUR L'AUTHENTIFICATION (ENTITÉ)
     
    public function getUtilisateurEntityByEmail(string $email): ?Utilisateur
    {
        return $this->utilisateurRepository->findOneBy(['email' => $email]);
    }

    // À UTILISER POUR LES RÉPONSES API (ARRAY)
     
    public function getUtilisateurByEmail(string $email): ?array
    {
        $user = $this->utilisateurRepository->findOneBy(['email' => $email]);

        return $user ? $this->formatUtilisateur($user) : null;
    }

    // Formatage sécurisé pour l'API
     
    public function formatUtilisateur(Utilisateur $user): array
    {
        return [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ];
    }
}