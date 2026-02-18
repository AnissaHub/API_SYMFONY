<?php

namespace App\DataFixtures;

use App\Entity\Utilisateur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher) {}

    public function load(ObjectManager $manager): void
    {
        $admin = new Utilisateur();
        $admin->setEmail('admin@mail.com');
        $admin->setRoles(['ROLE_ADMIN', 'ROLE_USER']);

        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'Admin123!'));
        $manager->persist($admin);

        $user = new Utilisateur();
        $user->setEmail('user@mail.com');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'User123!'));
        $manager->persist($user);

        $manager->flush();

     $user2 = new Utilisateur();
        $user2->setEmail('user2@mail.com');
        $user2->setRoles(['ROLE_USER']);
        $user2->setPassword(
            $this->passwordHasher->hashPassword($user2, 'userpassword2')
        );
        $manager->persist($user2);
        $manager->flush();







    }
   
}