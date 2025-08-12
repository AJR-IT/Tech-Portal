<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/user', name: 'app_user_')]
final class UserController extends AbstractController
{
    #[Route('/index', name: 'index')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/profile', name: 'profile')]
    public function userProfile(#[CurrentUser] User $currentUser): Response
    {
        return $this->render('user/profile.html.twig', [
            'user' => $currentUser,
        ]);
    }

    #[Route('/profile/update', name: 'profile_update')]
    public function userProfileUpdate(): void
    {
    }
}
