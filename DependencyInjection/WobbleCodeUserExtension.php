<?php

namespace WobbleCode\UserBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class WobbleCodeUserExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('wobble_code_user.app.available_languages', $config['app']['available_languages']);

        $container->setParameter('wobble_code_user.class.user', $config['class']['user']);
        $container->setParameter('wobble_code_user.class.organization', $config['class']['organization']);

        $container->setParameter('wobble_code_user.redirect.password_reset', $config['redirect']['password_reset']);
        $container->setParameter('wobble_code_user.redirect.signup_confirmed', $config['redirect']['signup_confirmed']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
