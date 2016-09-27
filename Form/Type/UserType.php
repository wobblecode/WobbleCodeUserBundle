<?php

namespace WobbleCode\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use WobbleCode\UIKitGeckoBundle\Form\Type\TagsType;

class UserType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'email',
                EmailType::class,
                array(
                    'horizontal' => false,
                    'translation_domain' => 'wc_user'
                )
            )
            ->add(
                'username',
                TextType::class,
                array(
                    'horizontal' => false,
                    'translation_domain' => 'wc_user'
                )
            )
            ->add(
                'plainPassword',
                TextType::class,
                array(
                    'horizontal' => false,
                    'translation_domain' => 'wc_user'
                )
            )
            ->add('plainPassword', RepeatedType::class, array(
                'horizontal' => false,
                'translation_domain' => 'wc_user',
                'type' => 'password',
                'options' => array('translation_domain' => 'UserBundle'),
                'first_options' => array(
                    'horizontal' => false,
                    'label'      => 'Password'
                ),
                'second_options' => array(
                    'horizontal' => false,
                    'translation_domain' => 'wc_user',
                    'label'      => 'Password Confirmation'
                )
            ))
            ->add(
                'roles',
                ChoiceType::class,
                array (
                    'label' => 'Choice expanded',
                    'choices'   => array(
                        'ROLE_USER'  => 'ROLE USER',
                        'ROLE_ADMIN' => 'ROLE ADMIN'
                    ),
                    'expanded' => true,
                    'multiple' => true,
                    'horizontal' => false,
                    'translation_domain' => 'wc_user'
                )
            )
            ->add(
                'tags',
                TagsType::class,
                array(
                    'horizontal_input_wrapper_class' => '',
                    'required' => false,
                    'pluginOptions' => array(
                        'tags' => [],
                        'containerCssClass' => 'form-control'
                    )
                )
            )
            ->add(
                'save',
                SubmitType::class
            )
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'render_fieldset' => false,
            'label_render'    => false,
            'show_legend'     => false,
            'data_class'      => 'WobbleCode\UserBundle\Document\User'
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'wobblecode_user_user';
    }
}
