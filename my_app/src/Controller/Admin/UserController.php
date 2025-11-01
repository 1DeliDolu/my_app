<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/users')]
final class UserController extends AbstractController
{
    #[Route('/', name: 'app_admin_users', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('admin/users/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/{id}/role', name: 'app_admin_user_role', methods: ['POST'])]
    public function changeRole(User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('change_role_'.$user->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('danger', 'Invalid security token.');

            return $this->redirectToRoute('app_admin_users');
        }

        $role = (string) $request->request->get('role');
        $allowedRoles = ['ROLE_ADMIN', 'ROLE_EMPLOYEE', 'ROLE_CUSTOMER'];

        if (!\in_array($role, $allowedRoles, true)) {
            $this->addFlash('danger', 'Unsupported role selection.');

            return $this->redirectToRoute('app_admin_users');
        }

        $user->setRoles([$role]);
        $user->setType(strtolower(str_replace('ROLE_', '', $role)));
        $entityManager->flush();

        $this->addFlash('success', 'User role updated!');

        return $this->redirectToRoute('app_admin_users');
    }
}
