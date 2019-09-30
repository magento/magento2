<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Dto\Mock;

use Magento\Framework\Dto\DtoConfig;
use Magento\Framework\Dto\Projection\Processor\TestPostprocessor;
use Magento\Framework\Dto\Projection\Processor\TestPostProcessorNestedDto;
use Magento\Framework\Dto\Projection\Processor\TestPreprocessor;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Configure test projectors for integration tests
 */
class ConfigureTestProjections
{
    /**
     * @inheritDoc
     */
    public static function execute(): void
    {
        /** @var DtoConfig $config */
        $config = Bootstrap::getObjectManager()->get(DtoConfig::class);

        $config->merge([
            'projection' => [
                'Magento\Framework\Dto\Mock\ImmutableDtoTwoInterface' => [
                    'Magento\Framework\Dto\Mock\ImmutableDtoInterface' => [
                        'preprocessor' => [
                            TestPreprocessor::class,
                        ],
                        'straight' => [
                            'prop_one' => 'prop1',
                            'prop_two' => 'prop2',
                            'propThree' => 'prop3',
                            'propFour' => 'prop4',
                        ],
                        'postprocessor' => [
                            TestPostprocessor::class,
                        ],
                    ]
                ],
                'Magento\Framework\Dto\Mock\ImmutableNestedDtoInterface' => [
                    'Magento\Framework\Dto\Mock\ImmutableDtoInterface' => [
                        'straight' => [
                            'id' => 'prop1',
                            'test_dto1.prop1' => 'prop1',
                            'test_dto1.prop2' => 'prop2',
                            'test_dto1.prop3' => 'prop3',
                            'test_dto1.prop4' => 'prop4',
                            'testDto2.prop1' => 'prop1',
                            'testDto2.prop2' => 'prop2',
                            'testDto2.prop3' => 'prop3',
                            'testDto2.prop4' => 'prop4'
                        ],
                        'postprocessor' => [
                            TestPostProcessorNestedDto::class,
                        ],
                    ]
                ],
                'Magento\Framework\Dto\Mock\ImmutableDtoInterface' => [
                    'Magento\Framework\Dto\Mock\ImmutableNestedDtoInterface' => [
                        'straight' => [
                            'prop1' => 'test_dto1.prop1',
                            'prop2' => 'test_dto1.prop2',
                            'prop3' => 'testDto1.prop3',
                            'prop4' => 'testDto1.prop4',
                        ]
                    ]
                ]
            ]
        ]);
    }
}
