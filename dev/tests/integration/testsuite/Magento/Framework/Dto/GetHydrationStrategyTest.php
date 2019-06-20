<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Dto;

use Magento\Framework\Dto\DtoProcessor\GetHydrationStrategy;
use Magento\Framework\Dto\Mock\ImmutableDto;
use Magento\Framework\Dto\Mock\MutableDto;
use Magento\Framework\Dto\Mock\MockDtoConfig;
use Magento\Framework\Dto\Mock\TestSimpleObject;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class GetHydrationStrategyTest extends TestCase
{
    /**
     * @var GetHydrationStrategy
     */
    private $getHydrationStrategy;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();

        $objectManager->configure([
            'preferences' => [
                DtoConfig::class => MockDtoConfig::class
            ]
        ]);

        $this->getHydrationStrategy = $objectManager->get(GetHydrationStrategy::class);
    }

    /**
     * @return array
     */
    public function hydrationStrategyDataProvider(): array
    {
        return [
            'ImmutableDto' => [
                'className' => ImmutableDto::class,
                'data' => [
                    'prop1' => 1,
                    'prop2' => 'b',
                    'prop3' => ['abc'],
                    'prop4' => [1],
                ],
                'expected' => [
                    GetHydrationStrategy::HYDRATOR_STRATEGY_SETTER => [],
                    GetHydrationStrategy::HYDRATOR_STRATEGY_CONSTRUCTOR_PARAM => [
                        'prop1' => ['parameter' => 'prop1', 'type' => 'int'],
                        'prop2' => ['parameter' => 'prop2', 'type' => 'string'],
                        'prop3' => ['parameter' => 'prop3', 'type' => 'array'],
                        'prop4' => ['parameter' => 'prop4', 'type' => 'int[]'],
                    ],
                    GetHydrationStrategy::HYDRATOR_STRATEGY_CONSTRUCTOR_DATA => [],
                    GetHydrationStrategy::HYDRATOR_STRATEGY_ORPHAN => [],
                ]
            ],
            'ImmutableDtoWithOrphans' => [
                'className' => ImmutableDto::class,
                'data' => [
                    'prop1' => 1,
                    'prop2' => 'b',
                    'prop3' => ['abc'],
                    'prop4' => [1],
                    'nonExistingProp' => 1
                ],
                'expected' => [
                    GetHydrationStrategy::HYDRATOR_STRATEGY_SETTER => [],
                    GetHydrationStrategy::HYDRATOR_STRATEGY_CONSTRUCTOR_PARAM => [
                        'prop1' => ['parameter' => 'prop1', 'type' => 'int'],
                        'prop2' => ['parameter' => 'prop2', 'type' => 'string'],
                        'prop3' => ['parameter' => 'prop3', 'type' => 'array'],
                        'prop4' => ['parameter' => 'prop4', 'type' => 'int[]'],
                    ],
                    GetHydrationStrategy::HYDRATOR_STRATEGY_CONSTRUCTOR_DATA => [],
                    GetHydrationStrategy::HYDRATOR_STRATEGY_ORPHAN => [
                        'nonExistingProp'
                    ],
                ]
            ],
            'MutableDto' => [
                'className' => MutableDto::class,
                'data' => [
                    'prop1' => 1,
                    'prop2' => 'b',
                    'prop3' => ['abc'],
                    'prop4' => [1]
                ],
                'expected' => [
                    GetHydrationStrategy::HYDRATOR_STRATEGY_SETTER => [
                        'prop2' => ['method' => 'setProp2', 'type' => 'string'],
                        'prop4' => ['method' => 'setProp4', 'type' => 'int[]'],
                    ],
                    GetHydrationStrategy::HYDRATOR_STRATEGY_CONSTRUCTOR_PARAM => [
                        'prop1' => ['parameter' => 'prop1', 'type' => 'int'],
                        'prop3' => ['parameter' => 'prop3', 'type' => 'array'],
                    ],
                    GetHydrationStrategy::HYDRATOR_STRATEGY_CONSTRUCTOR_DATA => [],
                    GetHydrationStrategy::HYDRATOR_STRATEGY_ORPHAN => [],
                ]
            ],
            'SimpleObject' => [
                'className' => TestSimpleObject::class,
                'data' => [
                    'prop1' => 'a',
                    'prop2' => 'b'
                ],
                'expected' => [
                    GetHydrationStrategy::HYDRATOR_STRATEGY_SETTER => [],
                    GetHydrationStrategy::HYDRATOR_STRATEGY_CONSTRUCTOR_PARAM => [],
                    GetHydrationStrategy::HYDRATOR_STRATEGY_CONSTRUCTOR_DATA => [
                        'prop1' => ['type' => 'string'],
                        'prop2' => ['type' => 'string'],
                    ],
                    GetHydrationStrategy::HYDRATOR_STRATEGY_ORPHAN => [],
                ]
            ]
        ];
    }

    /**
     * @dataProvider hydrationStrategyDataProvider
     * @param string $className
     * @param array $data
     * @param array $expected
     * @throws ReflectionException
     */
    public function testHydrationStrategy(string $className, array $data, array $expected): void
    {
        self::assertEquals($expected, $this->getHydrationStrategy->execute($className, $data));
    }
}
