<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Service\UtilisateurService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class UtilisateurController extends AbstractController
{
    public function __construct(private UtilisateurService $utilisateurService) {}
   
  // voir son profil
  #[Route('/api/profile', name: 'api_profile', methods: ['GET'])]
   
    public function profile(): JsonResponse
    {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'utilisateur non trouvé'], 404);
        }

        return $this->json($this->utilisateurService->formatUtilisateur($user));
    }
 // mettre à jour son profil
 #[Route('/api/profile',name: 'api_profile_update', methods: ['PUT'])]
 public function update(Request $request, EntityManagerInterface $em): JsonResponse
 {
  /** @var \App\Entity\Utilisateur $user */
  $user = $this->getUser();
  if (!$user) {
    return $this->json(['message' => 'utilisateur non trouvé'], 404);
  }
  $data = json_decode($request->getContent(), true);
  
   $user = $this->utilisateurService->updateProfile($user, $data);
    $em->persist($user);
    $em->flush();
 
   return $this->json(['message' => 'profil mis à jour avec succés']);
 }
 // supprimer son compte
 #[Route('/api/profile', methods: ['DELETE'])]
 public function deleteProfile(EntityManagerInterface $em): JsonResponse
 {
     /** @var Utilisateur $user */
    $user = $this->getUser();

    $em->remove($user);
    $em->flush();

    return $this->json(['message' => 'Compte supprimé']);
 }
}