<?php

namespace App\Command;

use App\Repository\RentalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Commande pour fermer automatiquement les locations expirées
 */
#[AsCommand(
    name: 'app:rental:close-expired',
    description: 'Ferme automatiquement les locations dont la date de fin est dépassée',
)]
class RentalCloseExpiredCommand extends Command
{
    public function __construct(
        private readonly RentalRepository $rentalRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Fermeture des locations expirées');

        $expiredRentals = $this->rentalRepository->findExpiredRentals();
        $count = count($expiredRentals);

        if ($count === 0) {
            $io->success('Aucune location expirée à fermer.');
            return Command::SUCCESS;
        }

        $io->info(sprintf('Trouvé %d location(s) expirée(s) à fermer.', $count));

        $closed = 0;
        foreach ($expiredRentals as $rental) {
            $rental->setStatut('terminée');
            
            // Libérer le véhicule s'il n'y a pas d'autres locations actives
            $vehicle = $rental->getVehicle();
            if ($vehicle && !$this->rentalRepository->hasActiveRentalForVehicle($vehicle, $rental->getId())) {
                $vehicle->setStatut('disponible');
            }

            $closed++;
        }

        $this->entityManager->flush();

        $io->success(sprintf(
            '%d location(s) fermée(s) avec succès. Les véhicules ont été libérés si nécessaire.',
            $closed
        ));

        return Command::SUCCESS;
    }
}
