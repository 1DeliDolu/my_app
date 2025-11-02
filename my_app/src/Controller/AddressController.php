<?php

namespace App\Controller;

use App\Entity\Address;
use App\Form\AddressFormType;
use App\Repository\AddressRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/address')]
final class AddressController extends AbstractController
{
    #[Route('/', name: 'app_address_index', methods: ['GET'])]
    public function index(AddressRepository $addressRepository): Response
    {
        $addresses = $addressRepository->findBy(['user' => $this->getUser()]);

        return $this->render('address/index.html.twig', [
            'addresses' => $addresses,
        ]);
    }

    #[Route('/new', name: 'app_address_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $address = new Address();
        $form = $this->createForm(AddressFormType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $address->setUser($this->getUser());
            $entityManager->persist($address);
            $entityManager->flush();

            $this->addFlash('success', 'Address added successfully!');

            return $this->redirectToRoute('app_address_index');
        }

        return $this->render('address/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_address_edit', methods: ['GET', 'POST'])]
    public function edit(Address $address, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($address->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(AddressFormType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Address updated successfully!');

            return $this->redirectToRoute('app_address_index');
        }

        return $this->render('address/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_address_delete', methods: ['POST'])]
    public function delete(Address $address, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($address->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete_address_'.$address->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($address);
            $entityManager->flush();

            $this->addFlash('success', 'Address deleted successfully!');
        }

        return $this->redirectToRoute('app_address_index');
    }
}
