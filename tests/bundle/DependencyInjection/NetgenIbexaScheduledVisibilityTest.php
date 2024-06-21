<?php

declare(strict_types=1);

namespace Netgen\IbexaScheduledVisibility\Tests\DependencyInjection;

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
                    'type' => 'location',
                ],
            ],
            [
                [
                    'enabled' => false,
                    'type' => 'location',
                    'sections' => [
                        'visible' => [
                            'section_id' => 0,
                        ],
                        'hidden' => [
                            'section_id' => 0,
                        ],
                    ],
                ],
            ],
            [
                [
                    'enabled' => false,
                    'type' => 'location',
                    'sections' => [
                        'visible' => [
                            'section_id' => 0,
                        ],
                        'hidden' => [
                            'section_id' => 0,
                        ],
                    ],
                    'object_states' => [
                        'object_state_group_id' => 0,
                        'visible' => [
                            'object_state_id' => 0,
                        ],
                        'hidden' => [
                            'object_state_id' => 0,
                        ],
                    ],
                ],
            ],
            [
                [
                    'enabled' => false,
                    'type' => 'location',
                    'sections' => [
                        'visible' => [
                            'section_id' => 0,
                        ],
                        'hidden' => [
                            'section_id' => 0,
                        ],
                    ],
                    'object_states' => [
                        'object_state_group_id' => 0,
                        'visible' => [
                            'object_state_id' => 0,
                        ],
                        'hidden' => [
                            'object_state_id' => 0,
                        ],
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
                [],
                false,
            ],
            [
                [
                    'enabled' => false,
                ],
                false,
            ],
            [
                [
                    'enabled' => true,
                ],
                true,
            ],
        ];
    }

    public function provideTypeConfigurationCases(): iterable
    {
        return [
            [
                [
                    'type' => 'location',
                ],
                'location',
            ],
            [
                [
                    'type' => 'section',
                ],
                'section',
            ],
            [
                [
                    'type' => 'object_state',
                ],
                'object_state',
            ],
        ];
    }

    public function provideSectionConfigurationCases(): iterable
    {
        return [
            [
                [
                    'sections' => [
                        'visible' => [
                            'section_id' => 1,
                        ],
                        'hidden' => [
                            'section_id' => 2,
                        ],
                    ],
                ],
                [
                    'visible' => 1,
                    'hidden' => 2,
                ],
            ],
            [
                [
                    'sections' => [
                        'visible' => [
                            'section_id' => 2,
                        ],
                        'hidden' => [
                            'section_id' => 1,
                        ],
                    ],
                ],
                [
                    'visible' => 2,
                    'hidden' => 1,
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
                        'object_state_group_id' => 1,
                        'visible' => [
                            'object_state_id' => 2,
                        ],
                        'hidden' => [
                            'object_state_id' => 3,
                        ],
                    ],
                ],
                [
                    'group' => 1,
                    'visible' => 2,
                    'hidden' => 3,
                ],
            ],
            [
                [
                    'object_states' => [
                        'object_state_group_id' => 3,
                        'visible' => [
                            'object_state_id' => 2,
                        ],
                        'hidden' => [
                            'object_state_id' => 1,
                        ],
                    ],
                ],
                [
                    'group' => 3,
                    'visible' => 2,
                    'hidden' => 1,
                ],
            ],
        ];
    }

    public function provideContentTypesConfigurationCases(): iterable
    {
        return [
            [
                [
                    'content_types' => [],
                ],
                [
                    'all' => false,
                    'allowed' => [],
                ],
            ],
            [
                [
                    'content_types' => [
                        'all' => true,
                        'allowed' => [],
                    ],
                ],
                [
                    'all' => true,
                    'allowed' => [],
                ],
            ],
            [
                [
                    'content_types' => [
                        'all' => false,
                        'allowed' => ['content_1', 'content_2'],
                    ],
                ],
                [
                    'all' => false,
                    'allowed' => ['content_1', 'content_2'],
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
            'netgen_ibexa_scheduled_visibility.type',
            'location',
        );
        $this->assertContainerBuilderHasParameter(
            'netgen_ibexa_scheduled_visibility.sections.visible.section_id',
            0,
        );
        $this->assertContainerBuilderHasParameter(
            'netgen_ibexa_scheduled_visibility.sections.hidden.section_id',
            0,
        );
        $this->assertContainerBuilderHasParameter(
            'netgen_ibexa_scheduled_visibility.object_states.object_state_group_id',
            0,
        );
        $this->assertContainerBuilderHasParameter(
            'netgen_ibexa_scheduled_visibility.object_states.visible.object_state_id',
            0,
        );
        $this->assertContainerBuilderHasParameter(
            'netgen_ibexa_scheduled_visibility.object_states.hidden.object_state_id',
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
    public function testEnabledConfiguration(array $configuration, bool $expectedParameterValue): void
    {
        $this->load($configuration);

        $this->assertContainerBuilderHasParameter(
            'netgen_ibexa_scheduled_visibility.enabled',
            $expectedParameterValue,
        );
    }

    /**
     * @dataProvider provideTypeConfigurationCases
     */
    public function testTypeConfiguration(array $configuration, string $expectedParameterValue): void
    {
        $this->load($configuration);

        $this->assertContainerBuilderHasParameter(
            'netgen_ibexa_scheduled_visibility.type',
            $expectedParameterValue,
        );
    }

    /**
     * @dataProvider provideSectionConfigurationCases
     */
    public function testSectionConfiguration(array $configuration, array $expectedParameterValues): void
    {
        $this->load($configuration);

        $this->assertContainerBuilderHasParameter(
            'netgen_ibexa_scheduled_visibility.sections.visible.section_id',
            $expectedParameterValues['visible'],
        );
        $this->assertContainerBuilderHasParameter(
            'netgen_ibexa_scheduled_visibility.sections.hidden.section_id',
            $expectedParameterValues['hidden'],
        );
    }

    /**
     * @dataProvider provideObjectStateConfigurationCases
     */
    public function testObjectStateConfiguration(array $configuration, array $expectedParameterValues): void
    {
        $this->load($configuration);

        $this->assertContainerBuilderHasParameter(
            'netgen_ibexa_scheduled_visibility.object_states.object_state_group_id',
            $expectedParameterValues['group'],
        );
        $this->assertContainerBuilderHasParameter(
            'netgen_ibexa_scheduled_visibility.object_states.visible.object_state_id',
            $expectedParameterValues['visible'],
        );
        $this->assertContainerBuilderHasParameter(
            'netgen_ibexa_scheduled_visibility.object_states.hidden.object_state_id',
            $expectedParameterValues['hidden'],
        );
    }

    /**
     * @dataProvider provideContentTypesConfigurationCases
     */
    public function testContentTypesConfiguration(array $configuration, array $expectedParameterValues): void
    {
        $this->load($configuration);

        $this->assertContainerBuilderHasParameter(
            'netgen_ibexa_scheduled_visibility.content_types.all',
            $expectedParameterValues['all'],
        );
        $this->assertContainerBuilderHasParameter(
            'netgen_ibexa_scheduled_visibility.content_types.allowed',
            $expectedParameterValues['allowed'],
        );
    }

    protected function getContainerExtensions(): array
    {
        return [
            new NetgenIbexaScheduledVisibilityExtension(),
        ];
    }
}
