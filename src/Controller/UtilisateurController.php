<?php

namespace App\Controller;

use App\Service\UtilisateurService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class UtilisateurController extends AbstractController
{
    public function __construct(private UtilisateurService $utilisateurService) {}

#[Route('/api/utilisateurs', methods: ['GET'])]
 public function index(Request $request): JsonResponse
 {
   $users = $this->utilisateurService->getAllUtilisateurs();
   return $this->json(['data' => $users]);
 }
#[Route('/api/utilisateurs/{id}', methods: ['GET'])]
 public function show(int $id): JsonResponse
 {
   $user = $this->utilisateurService->getUtilisateurById($id);
   return $this->json(['data' => $user]);
 }

 #[Route('/api/utilisateurs', methods: ['POST'])]
 public function create(Request $request, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
 {
    $data = json_decode($request->getContent(), true);
 // Vérifier si l'utilisateur existe déjà
   $existingUser = $this->utilisateurService->emailExiste($data['email']);
    if ($existingUser) {
    return $this->json(['error' => "l'email existe déjà"], 400);
 }
    $utilisateur = $this->utilisateurService->createUtilisateur($data);

    $errors = $validator->validate($utilisateur);
    if (count($errors) > 0) {
        return $this->json(['errors' => (string) $errors], 400);
    }

    $em->persist($utilisateur);
    $em->flush();

    return $this->json([
        'message' => 'Utilisateur créé',
        'utilisateur' => $this->utilisateurService->formatUtilisateur($utilisateur)
    ], 201);
 }

 #[Route('/api/utilisateurs/{id}', methods: ['PUT'])]
 public function update(int $id, Request $request, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
 {
 $data = json_decode($request->getContent(), true);

 $user = $this->utilisateurService->getUtilisateurEntityById($id);
  if (!$user)
   {
    return $this->json(['message' => 'utilisateur non trouvé'], 404);
   }
  $this->utilisateurService->updateUtilisateur($user, $data);
    
  $em->flush();
  return $this->json([
  'message' => 'Utilisateur mis à jour avec succès',
  'user' => $this->utilisateurService->formatUtilisateur($user) ], 200);    // Retourne l’objet formaté et un message de succès.
       
 }

 #[Route('/api/utilisateurs/{id}', methods: ['DELETE'])]
 public function delete(int $id, EntityManagerInterface $em): JsonResponse
 {
  $user= $this->utilisateurService->getUtilisateurEntityById($id);
  if (!$user)
   {
    return $this->json(['message' => 'utilisateur non trouvé'], 404);
   }
    $em->remove($user);       // Doctrine prépare la suppression
    $em->flush();

        return $this->json(['message' => 'utilisateur supprimé avec succès'], 200);
  }
}