<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserAccessService
{
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function isConnectedUser(User $user) {
        return $this->tokenStorage->getToken()->getUser()->getId() === $user->getId();
    }

    public function isConnectedUserId($id) {
        return $this->tokenStorage->getToken()->getUser()->getId() === intval($id);
    }
}