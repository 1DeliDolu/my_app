<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

final class RegistrationController extends AbstractController
{
    public function __construct(
        private EmailVerifier $emailVerifier,
        private MailService $mailService
    ) {
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                (string) $form->get('plainPassword')->getData()
            );
            $user->setPassword($hashedPassword);
            $user->setRoles(['ROLE_CUSTOMER']);
            $user->setType('customer');
            $user->setIsVerified(false);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address('no-reply@myshop.com', 'MyShop'))
                    ->to($user->getEmail())
                    ->subject('Please confirm your email address')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
                    ->context([
                        'user' => $user,
                    ])
            );

            $this->addFlash('success', sprintf('We sent a confirmation email to %s. Please verify your account before logging in.', $user->getEmail()));

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, UserRepository $userRepository): Response
    {
        $id = $request->query->get('id');
        if (null === $id) {
            $this->addFlash('verify_email_error', 'Verification link is missing required information.');

            return $this->redirectToRoute('app_login');
        }

        $user = $userRepository->find($id);
        if (null === $user) {
            $this->addFlash('verify_email_error', 'Unable to find the account to verify.');

            return $this->redirectToRoute('app_login');
        }

        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirectToRoute('app_login');
        }

        $this->mailService->sendWelcomeEmail($user->getEmail(), $user->getFirstName());
        $this->addFlash('success', 'Your email address has been verified. You can now log in.');

        return $this->redirectToRoute('app_login');
    }
}
