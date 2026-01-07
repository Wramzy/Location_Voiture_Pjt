<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\Rental;
use App\Entity\Vehicle;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RentalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateDebut', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date de début',
            ])
            ->add('dateFin', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date de fin',
            ])
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'En cours' => 'en cours',
                    'Terminée' => 'terminée',
                ],
                'label' => 'Statut',
            ])
            ->add('client', EntityType::class, [
                'class' => Client::class,
                'choice_label' => static fn (Client $client) => sprintf('%s %s', $client->getPrenom(), $client->getNom()),
                'placeholder' => 'Choisir un client',
            ])
            ->add('vehicle', EntityType::class, [
                'class' => Vehicle::class,
                'choice_label' => static fn (Vehicle $vehicle) => sprintf('%s %s (%s)', $vehicle->getMarque(), $vehicle->getModele(), $vehicle->getImmatriculation()),
                'placeholder' => 'Choisir un véhicule',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Rental::class,
        ]);
    }
}
