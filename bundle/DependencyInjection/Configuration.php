<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaScheduledVisibilityBundle\DependencyInjection;

use Netgen\Bundle\IbexaScheduledVisibilityBundle\Enums\HandlerType;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    private string $rootNodeName;

    public function __construct(string $rootNodeName)
    {
        $this->rootNodeName = $rootNodeName;
    }

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder($this->rootNodeName);
        $rootNode = $treeBuilder->getRootNode();

        $this->addEnabled($rootNode);
        $this->addTypeSection($rootNode);
        $this->addContentTypesSection($rootNode);
        $this->addSectionsSection($rootNode);
        $this->addObjectStatesSection($rootNode);

        return $treeBuilder;
    }

    private function addEnabled(ArrayNodeDefinition $nodeDefinition): void
    {
        $nodeDefinition
            ->treatFalseLike(['enabled' => false])
            ->treatTrueLike(['enabled' => true])
            ->treatNullLike(['enabled' => false])
                ->children()
                    ->booleanNode('enabled')
                        ->defaultFalse()
                    ->end()
                ->end();
    }

    private function addTypeSection(ArrayNodeDefinition $nodeDefinition): void
    {
        $nodeDefinition
            ->children()
                ->scalarNode('type')
                    ->info('Configure type for scheduled visibility mechanism')
                    ->defaultValue('location')
                ->end()
            ->end();
    }

    private function addContentTypesSection(ArrayNodeDefinition $nodeDefinition): void
    {
        $nodeDefinition
            ->children()
                ->arrayNode('content_types')
                    ->info('Configure content_types used for scheduled visibility mechanism')
                    ->addDefaultsIfNotSet()
                    ->treatFalseLike(['enabled' => false])
                    ->treatTrueLike(['enabled' => true])
                    ->treatNullLike(['enabled' => false])
                    ->children()
                        ->booleanNode('all')
                            ->defaultFalse()
                        ->end()
                        ->arrayNode('allowed')
                            ->scalarPrototype()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addSectionsSection(ArrayNodeDefinition $nodeDefinition): void
    {
        $nodeDefinition
            ->children()
                ->arrayNode('sections')
                    ->info('Configure sections used for scheduled visibility mechanism')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('visible')
                            ->info('Configure visible section id used for scheduled visibility mechanism')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->integerNode('section_id')
                                    ->defaultValue(0)
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('hidden')
                            ->info('Configure hidden section id used for scheduled visibility mechanism')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->integerNode('section_id')
                                    ->defaultValue(0)
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addObjectStatesSection(ArrayNodeDefinition $nodeDefinition): void
    {
        $nodeDefinition
            ->children()
                ->arrayNode('object_states')
                    ->info('Configure object_states used for scheduled visibility mechanism')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('object_state_group_id')
                            ->defaultValue(0)
                         ->end()
                        ->arrayNode('visible')
                            ->info('Configure visible object states and groups used for scheduled visibility mechanism')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->integerNode('object_state_id')
                                    ->defaultValue(0)
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('hidden')
                            ->info('Configure hidden bject states and groups used for scheduled visibility mechanism')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->integerNode('object_state_id')
                                    ->defaultValue(0)
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
