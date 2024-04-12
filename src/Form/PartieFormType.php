<?php

namespace App\Form;

use App\Entity\Partie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class PartieFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('datePrevue', DateType::class)
            ->add('creneauHoraire', ChoiceType::class, [
                'choices' => [
                    'Matinée' => 'Matinée',
                    'Heure du déjeuner' => 'Heure du déjeuner',
                    'Après-midi' => 'Après-midi',
                    'Début de Soirée' => 'Début de Soirée',
                ],
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('club', ChoiceType::class, [
                'choices' => [
                    'Club Gammarth' => 'Club Gammarth',
                    'Club Sousse' => 'Club Sousse',
                    'Club Hammamet' => 'Club Hammamet',
                ],
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('commentaire', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Partie::class,
        ]);
    }
}
