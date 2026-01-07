<?php

namespace App\Form;

use App\Entity\Vehicle;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VehicleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('marque')
            ->add('modele')
            ->add('annee')
            ->add('immatriculation')
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'Disponible' => 'disponible',
                    'Loué' => 'loué',
                    'Maintenance' => 'maintenance',
                ],
                'label' => 'Statut',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vehicle::class,
        ]);
    }
}
