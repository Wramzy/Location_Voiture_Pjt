<?php

namespace App\Controller;

use App\Repository\ClientRepository;
use App\Repository\RentalRepository;
use App\Repository\VehicleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(
        VehicleRepository $vehicleRepository,
        ClientRepository $clientRepository,
        RentalRepository $rentalRepository
    ): Response {
        $totalVehicles = $vehicleRepository->count([]);
        $availableVehicles = $vehicleRepository->count(['statut' => 'disponible']);
        $rentedVehicles = $vehicleRepository->count(['statut' => 'loué']);
        $totalClients = $clientRepository->count([]);
        $activeRentals = $rentalRepository->count(['statut' => 'en cours']);
        $totalRentals = $rentalRepository->count([]);

        return $this->render('dashboard/index.html.twig', [
            'totalVehicles' => $totalVehicles,
            'availableVehicles' => $availableVehicles,
            'rentedVehicles' => $rentedVehicles,
            'totalClients' => $totalClients,
            'activeRentals' => $activeRentals,
            'totalRentals' => $totalRentals,
        ]);
    }
}
