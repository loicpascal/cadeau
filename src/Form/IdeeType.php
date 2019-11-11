<?php

namespace App\Form;

use App\Entity\Idee;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IdeeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('libelle', TextType::class, ['label' => 'Libellé'])
            ->add('commentaire', TextareaType::class, ['required' => false])
            ->add('url', UrlType::class, ['required' => false]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            $idee = $event->getData();
            $form = $event->getForm();

            if (!$idee || $idee->getId() === null) {
                $form->add('save', SubmitType::class, ['label' => 'Ajouter']);
            } else {
                $form->add('save', SubmitType::class, ['label' => 'Modifier']);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Idee::class
        ]);
    }
}
