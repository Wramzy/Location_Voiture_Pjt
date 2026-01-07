<?php

namespace App\Controller;

use App\Entity\Rental;
use App\Form\RentalType;
use App\Repository\RentalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\FormError;

#[Route('/rental')]
final class RentalController extends AbstractController
{
    #[Route(name: 'app_rental_index', methods: ['GET'])]
    public function index(RentalRepository $rentalRepository): Response
    {
        return $this->render('rental/index.html.twig', [
            'rentals' => $rentalRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_rental_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, RentalRepository $rentalRepository): Response
    {
        $rental = new Rental();
        $form = $this->createForm(RentalType::class, $rental);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $vehicle = $rental->getVehicle();

            if (!$vehicle) {
                $form->addError(new FormError('Un véhicule est requis.'));
            } elseif ($vehicle->getStatut() === 'loué' || $rentalRepository->hasActiveRentalForVehicle($vehicle)) {
                $form->get('vehicle')->addError(new FormError('Ce véhicule est déjà loué.'));
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            // force rental to start as in progress and mark vehicle unavailable
            $rental->setStatut('en cours');
            $rental->getVehicle()?->setStatut('loué');

            $entityManager->persist($rental);
            $entityManager->flush();

            $this->addFlash('success', 'Location créée et véhicule marqué comme loué.');

            return $this->redirectToRoute('app_rental_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('rental/new.html.twig', [
            'rental' => $rental,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_rental_show', methods: ['GET'])]
    public function show(Rental $rental): Response
    {
        return $this->render('rental/show.html.twig', [
            'rental' => $rental,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_rental_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Rental $rental, EntityManagerInterface $entityManager, RentalRepository $rentalRepository): Response
    {
        $form = $this->createForm(RentalType::class, $rental);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $vehicle = $rental->getVehicle();

            if ($rental->getStatut() === 'en cours' && $vehicle && $rentalRepository->hasActiveRentalForVehicle($vehicle, $rental->getId())) {
                $form->get('vehicle')->addError(new FormError('Ce véhicule est déjà loué.'));
            } else {
                if ($rental->getStatut() === 'terminée' && $vehicle) {
                    $vehicle->setStatut('disponible');
                } elseif ($rental->getStatut() === 'en cours' && $vehicle) {
                    $vehicle->setStatut('loué');
                }

                $entityManager->flush();

                $this->addFlash('success', 'Location mise à jour.');

                return $this->redirectToRoute('app_rental_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('rental/edit.html.twig', [
            'rental' => $rental,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_rental_delete', methods: ['POST'])]
    public function delete(Request $request, Rental $rental, EntityManagerInterface $entityManager, RentalRepository $rentalRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$rental->getId(), $request->getPayload()->getString('_token'))) {
            $vehicle = $rental->getVehicle();
            $entityManager->remove($rental);
            $entityManager->flush();

            if ($vehicle && !$rentalRepository->hasActiveRentalForVehicle($vehicle)) {
                $vehicle->setStatut('disponible');
                $entityManager->flush();
            }

            $this->addFlash('success', 'Location supprimée.');
        }

        return $this->redirectToRoute('app_rental_index', [], Response::HTTP_SEE_OTHER);
    }
}
