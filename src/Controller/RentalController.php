<?php

namespace App\Controller;

use App\Entity\Rental;
use App\Form\RentalType;
use App\Repository\RentalRepository;
use App\Service\RentalService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\FormError;

#[Route('/rental')]
final class RentalController extends AbstractController
{
    public function __construct(
        private readonly RentalService $rentalService
    ) {
    }

    #[Route(name: 'app_rental_index', methods: ['GET'])]
    public function index(
        RentalRepository $rentalRepository,
        Request $request,
        \Knp\Component\Pager\PaginatorInterface $paginator
    ): Response {
        $search = $request->query->get('search');
        $statut = $request->query->get('statut');

        $queryBuilder = $rentalRepository->createQueryBuilder('r')
            ->leftJoin('r.client', 'c')
            ->leftJoin('r.vehicle', 'v')
            ->addSelect('c', 'v');

        if ($search) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->like('c.nom', ':search'),
                    $queryBuilder->expr()->like('c.prenom', ':search'),
                    $queryBuilder->expr()->like('v.marque', ':search'),
                    $queryBuilder->expr()->like('v.modele', ':search'),
                    $queryBuilder->expr()->like('v.immatriculation', ':search')
                )
            )->setParameter('search', '%' . $search . '%');
        }

        if ($statut) {
            $queryBuilder->andWhere('r.statut = :statut')
                ->setParameter('statut', $statut);
        }

        $queryBuilder->orderBy('r.dateDebut', 'DESC');

        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            10 // Nombre d'éléments par page
        );

        return $this->render('rental/index.html.twig', [
            'rentals' => $pagination,
            'search' => $search,
            'statut' => $statut,
        ]);
    }

    #[Route('/new', name: 'app_rental_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $rental = new Rental();
        $form = $this->createForm(RentalType::class, $rental);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $vehicle = $rental->getVehicle();

            if (!$vehicle) {
                $form->addError(new FormError('Un véhicule est requis.'));
            } elseif (!$this->rentalService->isVehicleAvailable($vehicle, $rental->getDateDebut(), $rental->getDateFin())) {
                $form->get('vehicle')->addError(new FormError('Ce véhicule n\'est pas disponible pour cette période.'));
            } else {
                try {
                    $this->rentalService->createRental($rental);
                    $this->addFlash('success', 'Location créée avec succès. Le véhicule a été marqué comme loué.');

                    return $this->redirectToRoute('app_rental_index', [], Response::HTTP_SEE_OTHER);
                } catch (\RuntimeException $e) {
                    $form->addError(new FormError($e->getMessage()));
                }
            }
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
    public function edit(Request $request, Rental $rental): Response
    {
        $form = $this->createForm(RentalType::class, $rental);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $vehicle = $rental->getVehicle();

            // Vérifier la disponibilité si la location passe à "en cours"
            if ($rental->getStatut() === 'en cours' && $vehicle) {
                if (!$this->rentalService->isVehicleAvailable($vehicle, $rental->getDateDebut(), $rental->getDateFin(), $rental->getId())) {
                    $form->get('vehicle')->addError(new FormError('Ce véhicule n\'est pas disponible pour cette période.'));
                } else {
                    try {
                        $this->rentalService->updateRental($rental);
                        $this->addFlash('success', 'Location mise à jour avec succès.');

                        return $this->redirectToRoute('app_rental_index', [], Response::HTTP_SEE_OTHER);
                    } catch (\RuntimeException $e) {
                        $form->addError(new FormError($e->getMessage()));
                    }
                }
            } else {
                try {
                    $this->rentalService->updateRental($rental);
                    $this->addFlash('success', 'Location mise à jour avec succès.');

                    return $this->redirectToRoute('app_rental_index', [], Response::HTTP_SEE_OTHER);
                } catch (\RuntimeException $e) {
                    $form->addError(new FormError($e->getMessage()));
                }
            }
        }

        return $this->render('rental/edit.html.twig', [
            'rental' => $rental,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_rental_delete', methods: ['POST'])]
    public function delete(Request $request, Rental $rental): Response
    {
        if ($this->isCsrfTokenValid('delete'.$rental->getId(), $request->getPayload()->getString('_token'))) {
            try {
                $this->rentalService->deleteRental($rental);
                $this->addFlash('success', 'Location supprimée avec succès.');
            } catch (\RuntimeException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->redirectToRoute('app_rental_index', [], Response::HTTP_SEE_OTHER);
    }
}
