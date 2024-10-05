<?php

namespace App\Form;

use App\Entity\Pilule;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PiluleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle')
            ->add('heureDePrise', null, [
                'widget' => 'single_text',
            ])
            ->add('tempsMaxi', null, [
                'widget' => 'single_text',
            ])
            ->add('nbPilulesPlaquette')
            ->add('nbJoursPause')
            ->add('dateDerniereReprise', null, [
                'widget' => 'single_text',
            ])
            ->add('proprietaire', EntityType::class, [
                'class' => Utilisateur::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Pilule::class,
        ]);
    }
}
