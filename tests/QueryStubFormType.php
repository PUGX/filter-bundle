<?php

namespace PUGX\FilterBundle\Tests;

use PUGX\FilterBundle\Form\AbstractFilterType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class QueryStubFormType extends AbstractFilterType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class, ['required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('method', 'QUERY');
    }

    public function getBlockPrefix(): string
    {
        return 'stub';
    }
}
