<?php

namespace WobbleCode\UserBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your
 * app/config files
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('wobble_code_user');

        $rootNode
            ->children()
                ->arrayNode('class')
                    ->children()
                        ->scalarNode('user')
                            ->defaultValue('WobbleCode\UserBundle\Document\User')
                        ->end()
                        ->scalarNode('organization')
                            ->defaultValue('WobbleCode\UserBundle\Model\OrganizationInterface')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('redirect')
                    ->children()
                        ->scalarNode('password_reset')->end()
                        ->scalarNode('signup_confirmed')->end()
                    ->end()
                ->end()
                ->arrayNode('app')
                    ->children()
                        ->arrayNode('available_languages')
                            ->prototype('scalar')->end()
                            ->defaultValue(['en'])
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
