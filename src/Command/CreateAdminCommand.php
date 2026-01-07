<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Commande pour créer l'utilisateur administrateur unique
 */
#[AsCommand(
    name: 'app:create-admin',
    description: 'Crée l\'utilisateur administrateur unique de l\'application',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Création de l\'utilisateur administrateur');

        // Vérifier si un utilisateur existe déjà
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy([]);
        
        if ($existingUser) {
            $io->warning('Un utilisateur existe déjà dans la base de données.');
            $io->info('Email: ' . $existingUser->getEmail());
            $io->info('Nom: ' . $existingUser->getNom() . ' ' . $existingUser->getPrenom());
            
            if (!$io->confirm('Voulez-vous le supprimer et en créer un nouveau ?', false)) {
                $io->info('Opération annulée.');
                return Command::SUCCESS;
            }
            
            $this->entityManager->remove($existingUser);
            $this->entityManager->flush();
        }

        // Créer le nouvel utilisateur admin
        $user = new User();
        $user->setEmail('admin@locauto.com');
        $user->setNom('Administrateur');
        $user->setPrenom('Locauto');
        $user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
        
        // Hasher le mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            'Locauto2024!'
        );
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('Utilisateur administrateur créé avec succès !');
        $io->table(
            ['Champ', 'Valeur'],
            [
                ['Email', 'admin@locauto.com'],
                ['Mot de passe', 'Locauto2024!'],
                ['Nom', 'Administrateur'],
                ['Prénom', 'Locauto'],
            ]
        );

        $io->note('Vous pouvez maintenant vous connecter avec ces identifiants.');

        return Command::SUCCESS;
    }
}
