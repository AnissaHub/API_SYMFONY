<?php

namespace App\Security\Voter;

use App\Entity\Car;
use App\Entity\Utilisateur;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class CarVoter extends Voter
{
    public const EDIT = 'CAR_EDIT';
    public const VIEW = 'CAR_VIEW';
    public const DELETE = 'CAR_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [
            self::EDIT,
            self::VIEW,
            self::DELETE
        ]) && $subject instanceof Car;
    }
  
    protected function voteOnAttribute(string $attribute, mixed $car, TokenInterface $token): bool
    {
        /** @var Utilisateur $user */
        $user = $token->getUser();
         // Si pas connecté
        if (!$user instanceof Utilisateur) {
            return false;
        }

        //  ADMIN peut tout faire
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        // OWNER peut agir sur sa voiture
        return $car->getUtilisateur() === $user;
    }
}
