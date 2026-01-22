<?php

namespace App\Service;

use App\Repository\UtilisateurRepository;
use App\Entity\Utilisateur;

class UtilisateurService
{
    public function __construct(private UtilisateurRepository $utilisateurRepository) {}

    // Retourne tous les utilisateurs
    public function getAllUtilisateurs(): array
    {
        $utilisateurs = $this->utilisateurRepository->findAll();
        $result = [];
        foreach ($utilisateurs as $user) {
            $result[] = $this->formatUtilisateur($user);
        }
        return $result;
    }

    // Retourne un utilisateur par ID
    public function getUtilisateurById(int $id): ?array
    {
        $user = $this->utilisateurRepository->find($id);
        return $user ? $this->formatUtilisateur($user) : null;
    }

    // Retourne l'entité Utilisateur par ID (pour update/delete)
    public function getUtilisateurEntityById(int $id): ?Utilisateur
    {
        return $this->utilisateurRepository->find($id);
    }

    // Crée un nouvel utilisateur à partir d'un tableau $data
    public function createUtilisateur(array $data): Utilisateur
    {
        $user = new Utilisateur();
        $user->setEmail($data['email']);
        $user->setPassword($data['password']); 
        $user->setRoles($data['roles']);
        $user->setCreateAt(new \DateTimeImmutable());
        return $user;
    }
    
    // Met à jour un utilisateur existant
    public function updateUtilisateur(Utilisateur $user, array $data): void
    {
    
        $user->setEmail($data['email']);
        $user->setPassword($data['password']); 
        $user->setRoles($data['roles']);
       if (isset($data['createAt'])) {
       $user->setCreateAt(new \DateTimeImmutable($data['createAt']));
}
    }

    public function emailExiste(string $email): bool
    {
     
     $email = strtolower(trim($email));
    
     return $this->utilisateurRepository->findOneBy(['email' => $email]) !== null;
    }
    // Transforme un utilisateur en tableau pour JSON
    public function formatUtilisateur(Utilisateur $user): array
{
    return [
        'id' => $user->getId(),
        'email' => $user->getEmail(),
        'password' => $user->getPassword(),
        'roles' => $user->getRoles(),
        'createdAt' => $user->getCreateAt()?->format('Y-m-d H:i:s'),
    ];
}



}