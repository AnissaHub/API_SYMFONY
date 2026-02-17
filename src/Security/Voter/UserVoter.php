<?php

namespace App\Security\Voter;

use App\Entity\Utilisateur;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class UserVoter extends Voter
{
    public const EDIT = 'USER_EDIT';
    public const VIEW = 'USER_VIEW';
    public const DELETE = 'USER_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [
            self::EDIT,
            self::VIEW,
            self::DELETE
        ]) && $subject instanceof Utilisateur;
    }
  
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var Utilisateur $userTarget */
        $user = $token->getUser();
         // Si pas connecté
        if (!$user instanceof Utilisateur) {
            return false;
        }

        //  ADMIN peut tout faire
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

       // uSER normaux ne peuvent agir que sur eux memes
       switch ($attribute){
       case self::VIEW:
       case self::EDIT:
       case self::DELETE:
        return $userTarget->getId() === $user->getId();

       }
       return false;
    }
}
