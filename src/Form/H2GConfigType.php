<?php

namespace App\Form;

use App\Entity\H2GConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class H2GConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('harvest_id', ChoiceType::class, [
              'choice_loader' => new CallbackChoiceLoader(function() {
                return; // @todo
              })
            ])
            ->add('gitlab_id')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => H2GConfig::class,
        ]);
    }
}
