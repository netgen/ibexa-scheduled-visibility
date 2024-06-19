<?php

declare(strict_types=1);

namespace Netgen\IbexaSearchExtraBundle\Tests\DependencyInjection;

use Exception;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Netgen\Bundle\IbexaScheduledVisibilityBundle\DependencyInjection\NetgenIbexaScheduledVisibilityExtension;

final class NetgenIbexaScheduledVisibilityTest extends AbstractExtensionTestCase
{
    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->setParameter('kernel.bundles', []);
    }

    public function provideDefaultConfigurationCases(): iterable
    {
        return [
            [
                [],
            ],
            [
                [
                    'enabled' => false,
                ],
            ],
            [
                [
                    'enabled' => false,
                    'strategy' => 'location',
                ],
            ],
            [
                [
                    'enabled' => false,
                    'strategy' => 'location',
                    'sections' => [
                        'visible_section_id' => 0,
                        'hidden_section_id' => 0,
                    ],
                ],
            ],
            [
                [
                    'enabled' => false,
                    'strategy' => 'location',
                    'sections' => [
                        'visible_section_id' => 0,
                        'hidden_section_id' => 0,
                    ],
                    'object_states' => [
                        'visible_object_state_id' => 0,
                        'hidden_object_state_id' => 0,
                    ],
                ],
            ],
            [
                [
                    'enabled' => false,
                    'strategy' => 'location',
                    'sections' => [
                        'visible_section_id' => 0,
                        'hidden_section_id' => 0,
                    ],
                    'object_states' => [
                        'visible_object_state_id' => 0,
                        'hidden_object_state_id' => 0,
                    ],
                    'content_types' => [
                        'all' => false,
                        'allowed' => [],
                    ],
                ],
            ],
        ];
    }

    public function provideEnabledConfigurationCases(): iterable
    {
        return [
            [
                [
                    'enabled' => true,
                ],
            ],
        ];
    }

    public function provideStrategyConfigurationCases(): iterable
    {
        return [
            [
                [
                    'strategy' => 'section',
                ],
            ],
        ];
    }

    public function provideSectionConfigurationCases(): iterable
    {
        return [
            [
                [
                    'sections' => [
                        'visible_section_id' => 1,
                        'hidden_section_id' => 2,
                    ],
                ],
            ],
        ];
    }

    public function provideObjectStateConfigurationCases(): iterable
    {
        return [
            [
                [
                    'object_states' => [
                        'visible_object_state_id' => 1,
                        'hidden_object_state_id' => 2,
                    ],
                ],
            ],
        ];
    }

    public function provideContentTypesConfigurationCases(): iterable
    {
        return [
            [
                [
                    'content_types' => [
                        'all' => true,
                        'allowed' => [
                            'content_type_1',
                            'content_type_2',
                            'content_type_3',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideDefaultConfigurationCases
     */
    public function testDefaultConfiguration(array $configuration): void
    {
        $this->load($configuration);

        $this->assertContainerBuilderHasParameter(
            'netgen_ibexa_scheduled_visibility.enabled',
            false,
        );
        $this->assertContainerBuilderHasParameter(
            'netgen_ibexa_scheduled_visibility.strategy',
            'location',
        );
        $this->assertContainerBuilderHasParameter(
            'netgen_ibexa_scheduled_visibility.sections.visible_section_id',
            0,
        );
        $this->assertContainerBuilderHasParameter(
            'netgen_ibexa_scheduled_visibility.sections.hidden_section_id',
            0,
        );
        $this->assertContainerBuilderHasParameter(
            'netgen_ibexa_scheduled_visibility.object_states.visible_object_state_id',
            0,
        );
        $this->assertContainerBuilderHasParameter(
            'netgen_ibexa_scheduled_visibility.object_states.hidden_object_state_id',
            0,
        );
        $this->assertContainerBuilderHasParameter(
            'netgen_ibexa_scheduled_visibility.content_types.all',
            false,
        );
        $this->assertContainerBuilderHasParameter(
            'netgen_ibexa_scheduled_visibility.content_types.allowed',
            [],
        );
    }

    /**
     * @dataProvider provideEnabledConfigurationCases
     */
    public function testEnabledConfiguration(array $configuration): void
    {
        $this->load($configuration);

        $this->assertContainerBuilderHasParameter(
            'netgen_ibexa_scheduled_visibility.enabled',
            true,
        );
    }

    /**
     * @dataProvider provideStrategyConfigurationCases
     */
    public function testStrategyConfiguration(array $configuration): void
    {
        $this->load($configuration);

        $this->assertContainerBuilderHasParameter(
            'netgen_ibexa_scheduled_visibility.strategy',
            'section',
        );
    }

    /**
     * @dataProvider provideSectionConfigurationCases
     */
    public function testSectionConfiguration(array $configuration): void
    {
        $this->load($configuration);

        $this->assertContainerBuilderHasParameter(
            'netgen_ibexa_scheduled_visibility.sections.visible_section_id',
            1,
        );
        $this->assertContainerBuilderHasParameter(
            'netgen_ibexa_scheduled_visibility.sections.hidden_section_id',
            2,
        );
    }

    /**
     * @dataProvider provideObjectStateConfigurationCases
     */
    public function testObjectStateConfiguration(array $configuration): void
    {
        $this->load($configuration);

        $this->assertContainerBuilderHasParameter(
            'netgen_ibexa_scheduled_visibility.object_states.visible_object_state_id',
            1,
        );
        $this->assertContainerBuilderHasParameter(
            'netgen_ibexa_scheduled_visibility.object_states.hidden_object_state_id',
            2,
        );
    }

    /**
     * @dataProvider provideContentTypesConfigurationCases
     */
    public function testContentTypesConfiguration(array $configuration): void
    {
        $this->load($configuration);

        $this->assertContainerBuilderHasParameter(
            'netgen_ibexa_scheduled_visibility.content_types.all',
            true,
        );
        $this->assertContainerBuilderHasParameter(
            'netgen_ibexa_scheduled_visibility.content_types.allowed',
            [
                'content_type_1',
                'content_type_2',
                'content_type_3',
            ],
        );
    }

    protected function getContainerExtensions(): array
    {
        return [
            new NetgenIbexaScheduledVisibilityExtension(),
        ];
    }
}
