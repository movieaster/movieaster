<?php

namespace Movieaster\MovieManagerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class PathType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('path')
        ;
    }

    public function getName()
    {
        return 'movieaster_moviemanagerbundle_pathtype';
    }
}
