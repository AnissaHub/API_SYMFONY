<?php

namespace App\Controller;


use App\Service\UtilisateurService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Utilisateur;

final class AuthController extends AbstractController
{
    public function __construct(
        private UtilisateurService $utilisateurService,
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
        private JWTTokenManagerInterface $jwtManager
    ) {}

    // ---------------- REGISTER ----------------
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['email']) || empty($data['password'])) {
            return new JsonResponse(['error' => 'Email et mot de passe requis'], 400);
        }

        if ($this->utilisateurService->emailExiste($data['email'])) {
            return new JsonResponse(['error' => 'Email déjà utilisé'], 409);
        }

        $user = $this->utilisateurService->createUtilisateur([
            'email' => $data['email'],
            'password' => $data['password'],
            'roles' => ['ROLE_USER'],
        ]);

        $this->em->persist($user);
        $this->em->flush();

        $result = $this->utilisateurService->formatUtilisateur($user);

        return new JsonResponse($result, 201);
    }

    // ---------------- LOGIN ----------------
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        // Récupérer l'entité Utilisateur (pas un tableau)
        $user = $this->utilisateurService->getUtilisateurEntityByEmail($email);

        if (!$user || !$this->passwordHasher->isPasswordValid($user, $password)) {
            return new JsonResponse(['error' => 'Email ou mot de passe invalide'], 401);
        }

        // Génération du token JWT
        $token = $this->jwtManager->create($user);

        return new JsonResponse([
            'token' => $token,
            'user' => $this->utilisateurService->formatUtilisateur($user),
        ]);
    }

    
}