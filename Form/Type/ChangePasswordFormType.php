<?php

namespace WobbleCode\UserBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\ChangePasswordFormType as BaseType;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class ChangePasswordFormType extends BaseType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $constraint = new UserPassword();

        $builder->add('current_password', PasswordType::class, [
            'horizontal'         => false,
            'translation_domain' => 'wc_user',
            'label'              => 'form.current_password',
            'translation_domain' => 'FOSUserBundle',
            'mapped'             => false,
            'constraints'        => $constraint,
        ]);

        $builder->add('plainPassword', RepeatedType::class, [
            'horizontal'         => false,
            'translation_domain' => 'wc_user',
            'type'               => 'password',
            'options'            => ['translation_domain' => 'FOSUserBundle'],
            'first_options'      => ['label' => 'form.new_password'],
            'second_options'     => ['label' => 'form.new_password_confirmation'],
            'invalid_message'    => 'fos_user.password.mismatch',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'wobblecode_user_change_password';
    }
}
