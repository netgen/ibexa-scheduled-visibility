<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaScheduledVisibilityBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    protected string $rootNodeName;

    public function __construct(string $rootNodeName)
    {
        $this->rootNodeName = $rootNodeName;
    }

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder($this->rootNodeName);
        $rootNode = $treeBuilder->getRootNode();

        $this->addEnabled($rootNode);
        $this->addStrategySection($rootNode);
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

    private function addStrategySection(ArrayNodeDefinition $nodeDefinition): void
    {
        $nodeDefinition
            ->children()
                ->enumNode('strategy')
                    ->info('Configure strategy for scheduled visibility mechanism')
                    ->values(['location', 'section', 'object_state'])
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
                        ->integerNode('visible_section_id')
                            ->defaultValue(0)
                        ->end()
                        ->integerNode('hidden_section_id')
                            ->defaultValue(0)
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
                        ->integerNode('visible_object_state_id')
                            ->defaultValue(0)
                        ->end()
                        ->integerNode('hidden_object_state_id')
                            ->defaultValue(0)
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
