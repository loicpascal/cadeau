<?php

namespace App\Form;

use App\Entity\Team;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Nom']);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            $team = $event->getData();
            $form = $event->getForm();

            if (!$team || $team->getId() === null) {
                $form->add('save', SubmitType::class, ['label' => 'Ajouter']);
            } else {
                $form->add('save', SubmitType::class, ['label' => 'Modifier']);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Team::class
        ]);
    }
}
