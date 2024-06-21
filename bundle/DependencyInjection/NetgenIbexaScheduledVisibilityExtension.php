<?php

declare(strict_types=1);

namespace Netgen\Bundle\IbexaScheduledVisibilityBundle\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class NetgenIbexaScheduledVisibilityExtension extends Extension
{
    public function getAlias(): string
    {
        return 'netgen_ibexa_scheduled_visibility';
    }

    public function getConfiguration(array $config, ContainerBuilder $container): Configuration
    {
        return new Configuration($this->getAlias());
    }

    /**
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new Loader\YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../../bundle/Resources/config/'),
        );

        $loader->load('services.yaml');

        $this->processExtensionConfiguration($configs, $container);
    }

    private function processExtensionConfiguration(array $configs, ContainerBuilder $container): void
    {
        $configuration = $this->getConfiguration($configs, $container);

        $configuration = $this->processConfiguration($configuration, $configs);

        $this->processEnabledConfiguration($configuration, $container);
        $this->processTypeConfiguration($configuration, $container);
        $this->processContentTypesConfiguration($configuration, $container);
        $this->processSectionsConfiguration($configuration, $container);
        $this->processObjectStatesConfiguration($configuration, $container);
    }

    private function processEnabledConfiguration(array $configuration, ContainerBuilder $container): void
    {
        $container->setParameter(
            'netgen_ibexa_scheduled_visibility.enabled',
            $configuration['enabled'],
        );
    }

    private function processTypeConfiguration(array $configuration, ContainerBuilder $container): void
    {
        $container->setParameter(
            'netgen_ibexa_scheduled_visibility.type',
            $configuration['type'],
        );
    }

    private function processContentTypesConfiguration(array $configuration, ContainerBuilder $container): void
    {
        $container->setParameter(
            'netgen_ibexa_scheduled_visibility.content_types.all',
            $configuration['content_types']['all'],
        );
        $container->setParameter(
            'netgen_ibexa_scheduled_visibility.content_types.allowed',
            $configuration['content_types']['allowed'],
        );
    }

    private function processSectionsConfiguration(array $configuration, ContainerBuilder $container): void
    {
        $container->setParameter(
            'netgen_ibexa_scheduled_visibility.sections.visible.section_id',
            $configuration['sections']['visible']['section_id'],
        );
        $container->setParameter(
            'netgen_ibexa_scheduled_visibility.sections.hidden.section_id',
            $configuration['sections']['hidden']['section_id'],
        );
    }

    private function processObjectStatesConfiguration(array $configuration, ContainerBuilder $container): void
    {
        $container->setParameter(
            'netgen_ibexa_scheduled_visibility.object_states.object_state_group_id',
            $configuration['object_states']['object_state_group_id'],
        );
        $container->setParameter(
            'netgen_ibexa_scheduled_visibility.object_states.visible.object_state_id',
            $configuration['object_states']['visible']['object_state_id'],
        );
        $container->setParameter(
            'netgen_ibexa_scheduled_visibility.object_states.hidden.object_state_id',
            $configuration['object_states']['hidden']['object_state_id'],
        );
    }
}
