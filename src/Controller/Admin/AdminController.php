<?php

namespace App\Controller\Admin;

use App\Service\UtilisateurService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/admin')]
#[IsGranted('ROLE_ADMIN')]    //seul un ADMIN peut y accéder

class AdminController extends AbstractController
{
    public function __construct(
        private UtilisateurService $utilisateurService,
        private EntityManagerInterface $em,
       
    ) {}


    #[Route('/users', name: 'admin_new_users', methods: ['POST'])]
    public function createUser(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

    //vérifier que les champs sont remplis et que email il est unique 
    if (empty($data['email']) || empty($data['password'])) {
            return new JsonResponse(['error' => 'Email et mot de passe requis'], 400);
        }

        if ($this->utilisateurService->emailExiste($data['email'])) {
            return new JsonResponse(['error' => 'Email déjà utilisé'], 409);
        }
        
       // Appel du service pour créer l'utilisateur
        $user = $this->utilisateurService->createUtilisateur($data);

        $this->em->persist($user);
        $this->em->flush();

        $result = $this->utilisateurService->formatUtilisateur($user);

        return new JsonResponse($result, 201);
    
        
    }
    //afficher tous les utilisateurs
    #[Route('/users', name: 'admin_users', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $users = $this->utilisateurService->getAllUsers(); // Service renvoie le tableau prêt pour JSON
        return $this-> json($users);
    }
    //afficher un user par son id
     #[Route('/users/{id}', name: 'admin_user_', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $user = $this->utilisateurService->getUtilisateurById($id); // Service renvoie le tableau prêt pour JSON
         if (!$user) {
            return $this->json(['message' => 'utilisateur non trouvé'], 404);
        }

      return $this->json([
        'user' => $this->utilisateurService->formatUtilisateur($user)
      ]);
    }



    //supprimer un user par son id
    #[Route('/users/{id}', name: 'admin_user_delete', methods: ['DELETE'])]
     public function delete( int $id, EntityManagerInterface $em): JsonResponse
     {
        $user = $this->utilisateurService->getUtilisateurById($id);

        if (!$user) {
            return $this->json(['message' => 'utilisateur non trouvée'], 404);
        }

        $em->remove($user);       // Doctrine prépare la suppression
        $em->flush();

        return $this->json(['message' => 'utilisateur supprimé avec succès'], 200);
     }
    
     //modification des roles
    #[Route('/users/{id}/roles', name: 'admin_roles_modif', methods: ['PATCH'])]
    public function updateRoles(int $id, Request $request, EntityManagerInterface $em): JsonResponse
   {
       $data = json_decode($request->getContent(), true);

       $user = $this->utilisateurService->getUtilisateurById($id);
       if (!$user)
       {
         return $this->json(['message' => 'utilisateur non trouvé'], 404);
       }
       $this->utilisateurService->updateUserRoles($id, $data['roles']);
    
       $em->flush();
      return $this->json([
     'message' => 'roles mis à jour avec succès',
     'user' => $this->utilisateurService->formatUtilisateur($user) ], 200);    // Retourne l’objet formaté et un message de succès.
       
    }
    //modification d'un utilisateur
    #[Route('/users/{id}', name: 'admin_modif', methods: ['PUT'])]
    public function update(int $id, Request $request, EntityManagerInterface $em): JsonResponse
   {
       $data = json_decode($request->getContent(), true);

       $user = $this->utilisateurService->getUtilisateurById($id);
       if (!$user)
       {
         return $this->json(['message' => 'utilisateur non trouvé'], 404);
       }
       $this->utilisateurService->updateProfile($user, $data);
    
       $em->flush();
      return $this->json([
     'message' => 'utilisateur mis à jour avec succès',
     'user' => $this->utilisateurService->formatUtilisateur($user) ], 200);    // Retourne l’objet formaté et un message de succès.
       
    }

}