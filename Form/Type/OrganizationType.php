<?php

namespace WobbleCode\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use WobbleCode\UIKitGeckoBundle\Form\Type\DocumentComboBoxType;
use WobbleCode\UIKitGeckoBundle\Form\Type\MarkdownType;
use WobbleCode\UIKitGeckoBundle\Form\Type\SwitchType;
use WobbleCode\UIKitGeckoBundle\Form\Type\TagsType;

class OrganizationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'owner',
                DocumentComboBoxType::class,
                [
                    'label'       => 'organization.owner.label',
                    'help_block'  => 'organization.owner.label',
                    'attr' => [
                        'placeholder' => 'organization.owner.label',
                    ],
                    'horizontal'  => false,
                    'class'       => 'WobbleCode\UserBundle\Document\User'
                ]
            )
            ->add(
                'enabled',
                SwitchType::class,
                [
                    'label'      => 'organization.enabled.label',
                    'help_block' => 'organization.enabled.help',
                    'horizontal' => false,
                    'state' => [
                        'on'  => 'success',
                        'off' => ''
                    ]
                ]
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
                'comment',
                MarkdownType::class,
                [
                    'label' => 'Comment',
                    'required' => false,
                    'horizontal' => false
                ]
            )
            ->add('save', 'submit', ['label' => 'organization.save.label'])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'wc_user',
            'render_fieldset' => false,
            'label_render'    => false,
            'show_legend'     => false,
            'data_class'      => 'WobbleCode\UserBundle\Model\OrganizationInterface'
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'wobblecode_user_organization';
    }
}
