<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Test\Unit\Factory;

use Magento\Framework\ObjectManager\Config\Config;
use Magento\Framework\ObjectManager\DefinitionInterface;
use Magento\Framework\ObjectManager\Factory\Dynamic\Developer;
use Magento\Framework\ObjectManager\FactoryInterface;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\OneScalar;
use Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\Polymorphous;
use Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\SemiVariadic;
use Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\Two;
use Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\Variadic;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Setup tests
     */
    protected function setUp(): void
    {
        $this->config = new Config();
        $this->factory = new Developer($this->config);
        $this->objectManager = new ObjectManager($this->factory, $this->config);
        $this->factory->setObjectManager($this->objectManager);
    }

    /**
     * Test create without args
     */
    public function testCreateNoArgs()
    {
        $this->assertInstanceOf('StdClass', $this->factory->create(\StdClass::class));
    }

    public function testResolveArgumentsException()
    {
        $this->expectException('UnexpectedValueException');
        $this->expectExceptionMessage('Invalid parameter configuration provided for $firstParam argument');
        $configMock = $this->createMock(Config::class);
        $configMock->expects($this->once())->method('getArguments')->willReturn(
            [
                'firstParam' => 1,
            ]
        );

        $definitionsMock = $this->getMockForAbstractClass(DefinitionInterface::class);
        $definitionsMock->expects($this->once())->method('getParameters')->willReturn(
            [
                [
                    'firstParam',
                    'string',
                    true,
                    'default_val',
                    false
                ]
            ]
        );

        $this->factory = new Developer(
            $configMock,
            null,
            $definitionsMock
        );
        $this->objectManager = new ObjectManager($this->factory, $this->config);
        $this->factory->setObjectManager($this->objectManager);
        $this->factory->create(
            OneScalar::class,
            ['foo' => 'bar']
        );
    }

    /**
     * Test create with one arg
     */
    public function testCreateOneArg()
    {
        /**
         * @var OneScalar $result
         */
        $result = $this->factory->create(
            OneScalar::class,
            ['foo' => 'bar']
        );
        $this->assertInstanceOf(OneScalar::class, $result);
        $this->assertEquals('bar', $result->getFoo());
    }

    /**
     * Test create with injectable
     */
    public function testCreateWithInjectable()
    {
        // let's imitate that One is injectable by providing DI configuration for it
        $this->config->extend(
            [
                OneScalar::class => [
                    'arguments' => ['foo' => 'bar'],
                ],
            ]
        );
        /**
         * @var Two $result
         */
        $result = $this->factory->create(Two::class);
        $this->assertInstanceOf(Two::class, $result);
        $this->assertInstanceOf(
            OneScalar::class,
            $result->getOne()
        );
        $this->assertEquals('bar', $result->getOne()->getFoo());
        $this->assertEquals('optional', $result->getBaz());
    }

    /**
     * @param        string $startingClass
     * @param        string $terminationClass
     * @dataProvider circularDataProvider
     */
    public function testCircular($startingClass, $terminationClass)
    {
        $this->expectException('\LogicException');
        $this->expectExceptionMessage(
            sprintf('Circular dependency: %s depends on %s and vice versa.', $startingClass, $terminationClass)
        );
        $this->factory->create($startingClass);
    }

    /**
     * @return array
     */
    public static function circularDataProvider()
    {
        $prefix = 'Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\\';
        return [
            ["{$prefix}CircularOne", "{$prefix}CircularThree"],
            ["{$prefix}CircularTwo", "{$prefix}CircularOne"],
            ["{$prefix}CircularThree", "{$prefix}CircularTwo"]
        ];
    }

    /**
     * Test create using reflection
     */
    public function testCreateUsingReflection()
    {
        $type = Polymorphous::class;
        $definitions = $this->getMockForAbstractClass(DefinitionInterface::class);
        // should be more than defined in "switch" of create() method
        $definitions->expects($this->once())->method('getParameters')->with($type)->willReturn(
            [
                ['one', null, false, null, false],
                ['two', null, false, null, false],
                ['three', null, false, null, false],
                ['four', null, false, null, false],
                ['five', null, false, null, false],
                ['six', null, false, null, false],
                ['seven', null, false, null, false],
                ['eight', null, false, null, false],
                ['nine', null, false, null, false],
                ['ten', null, false, null, false],
            ]
        );
        $factory = new Developer($this->config, null, $definitions);
        $result = $factory->create(
            $type,
            [
                'one' => 1,
                'two' => 2,
                'three' => 3,
                'four' => 4,
                'five' => 5,
                'six' => 6,
                'seven' => 7,
                'eight' => 8,
                'nine' => 9,
                'ten' => 10,
            ]
        );
        $this->assertSame(10, $result->getArg(9));
    }

    /**
     * Test create objects with variadic argument in constructor
     *
     * @param        $createArgs
     * @param        $expectedArg0
     * @param        $expectedArg1
     * @dataProvider testCreateUsingVariadicDataProvider
     */
    public function testCreateUsingVariadic(
        $createArgs,
        $expectedArg0,
        $expectedArg1
    ) {
        if (isset($createArgs['oneScalars'])) {
            if (is_array($createArgs['oneScalars'])) {
                foreach ($createArgs['oneScalars'] as &$args) {
                    if (is_callable($args)) {
                        $args = $args($this);
                    }
                }
            } else {
                if (is_callable($createArgs['oneScalars'])) {
                    $createArgs['oneScalars'] = $createArgs['oneScalars']($this);
                }
            }
        }

        if (is_callable($expectedArg0)) {
            $expectedArg0 = $expectedArg0($this);
        }

        if (is_callable($expectedArg1)) {
            $expectedArg1 = $expectedArg1($this);
        }

        $type = Variadic::class;
        $definitions = $this->getMockForAbstractClass(DefinitionInterface::class);

        $definitions->expects($this->once())->method('getParameters')->with($type)->willReturn(
            [
                [
                    'oneScalars',
                    OneScalar::class,
                    false,
                    [],
                    true
                ],
            ]
        );
        $factory = new Developer($this->config, null, $definitions);

        /**
         * @var \Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\Variadic $variadic
         */
        $variadic = is_null($createArgs)
            ? $factory->create($type)
            : $factory->create($type, $createArgs);

        $this->assertEquals($expectedArg0, $variadic->getOneScalarByKey(0));
        $this->assertEquals($expectedArg1, $variadic->getOneScalarByKey(1));
    }

    /**
     * @return array
     */
    public static function testCreateUsingVariadicDataProvider()
    {
        $oneScalar1 = static fn (self $testCase) => $testCase->createScalarMock();
        $oneScalar2 = static fn (self $testCase) => $testCase->createScalarMock();

        return [
            'without_args'    => [
                null,
                null,
                null,
            ],
            'with_empty_args' => [
                [],
                null,
                null,
            ],
            'with_empty_args_value' => [
                [
                    'oneScalars' => []
                ],
                null,
                null,
            ],
            'with_single_arg' => [
                [
                    'oneScalars' => $oneScalar1
                ],
                $oneScalar1,
                null,
            ],
            'with_full_args' => [
                [
                    'oneScalars' => [
                        $oneScalar1,
                        $oneScalar2,
                    ]
                ],
                $oneScalar1,
                $oneScalar2,
            ],
        ];
    }

    /**
     * Test data can be injected into variadic arguments from di config
     */
    public function testCreateVariadicFromDiConfig()
    {
        $oneScalar1 = $this->createMock(OneScalar::class);
        $oneScalar2 = $this->createMock(OneScalar::class);

        // let's imitate that Variadic is configured by providing DI configuration for it
        $this->config->extend(
            [
                Variadic::class => [
                    'arguments' => [
                        'oneScalars' => [
                            $oneScalar1,
                            $oneScalar2,
                        ]
                    ]
                ],
            ]
        );
        /**
         * @var \Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\Variadic $variadic
         */
        $variadic = $this->factory->create(Variadic::class);

        $this->assertSame($oneScalar1, $variadic->getOneScalarByKey(0));
        $this->assertSame($oneScalar2, $variadic->getOneScalarByKey(1));
    }

    /**
     * Test create objects with non variadic and variadic argument in constructor
     *
     * @param        $createArgs
     * @param        $expectedFooValue
     * @param        $expectedArg0
     * @param        $expectedArg1
     * @dataProvider testCreateUsingSemiVariadicDataProvider
     */
    public function testCreateUsingSemiVariadic(
        $createArgs,
        $expectedFooValue,
        $expectedArg0,
        $expectedArg1
    ) {
        if (isset($createArgs['oneScalars'])) {
            if (is_array($createArgs['oneScalars'])) {
                foreach ($createArgs['oneScalars'] as &$args) {
                    if (is_callable($args)) {
                        $args = $args($this);
                    }
                }
            } else {
                if (is_callable($createArgs['oneScalars'])) {
                    $createArgs['oneScalars'] = $createArgs['oneScalars']($this);
                }
            }
        }

        if (is_callable($expectedArg0)) {
            $expectedArg0 = $expectedArg0($this);
        }

        if (is_callable($expectedArg1)) {
            $expectedArg1 = $expectedArg1($this);
        }

        $type = SemiVariadic::class;
        $definitions = $this->getMockForAbstractClass(DefinitionInterface::class);

        $definitions->expects($this->once())->method('getParameters')->with($type)->willReturn(
            [
                [
                    'foo',
                    null,
                    false,
                    SemiVariadic::DEFAULT_FOO_VALUE,
                    false
                ],
                [
                    'oneScalars',
                    OneScalar::class,
                    false,
                    [],
                    true
                ],
            ]
        );
        $factory = new Developer($this->config, null, $definitions);

        /**
         * @var \Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\SemiVariadic $semiVariadic
         */
        $semiVariadic = is_null($createArgs)
            ? $factory->create($type)
            : $factory->create($type, $createArgs);

        $this->assertEquals($expectedFooValue, $semiVariadic->getFoo());
        $this->assertEquals($expectedArg0, $semiVariadic->getOneScalarByKey(0));
        $this->assertEquals($expectedArg1, $semiVariadic->getOneScalarByKey(1));
    }

    /**
     * @return array
     */
    public static function testCreateUsingSemiVariadicDataProvider()
    {
        $oneScalar1 = static fn (self $testCase) => $testCase->createScalarMock();
        $oneScalar2 = static fn (self $testCase) => $testCase->createScalarMock();

        return [
            'without_args'    => [
                null,
                SemiVariadic::DEFAULT_FOO_VALUE,
                null,
                null,
            ],
            'with_empty_args' => [
                [],
                SemiVariadic::DEFAULT_FOO_VALUE,
                null,
                null,
            ],
            'only_with_foo_value' => [
                [
                    'foo' => 'baz'
                ],
                'baz',
                null,
                null,
            ],
            'only_with_oneScalars_empty_value' => [
                [
                    'oneScalars' => []
                ],
                SemiVariadic::DEFAULT_FOO_VALUE,
                null,
                null,
            ],
            'only_with_oneScalars_single_value' => [
                [
                    'oneScalars' => $oneScalar1
                ],
                SemiVariadic::DEFAULT_FOO_VALUE,
                $oneScalar1,
                null,
            ],
            'only_with_oneScalars_full_value' => [
                [
                    'oneScalars' => [
                        $oneScalar1,
                        $oneScalar2,
                    ]
                ],
                SemiVariadic::DEFAULT_FOO_VALUE,
                $oneScalar1,
                $oneScalar2,
            ],
            'with_all_values_defined_in_right_order' => [
                [
                    'foo' => 'baz',
                    'oneScalars' => [
                        $oneScalar1,
                        $oneScalar2,
                    ]
                ],
                'baz',
                $oneScalar1,
                $oneScalar2,
            ],
            'with_all_values_defined_in_reverse_order' => [
                [
                    'oneScalars' => [
                        $oneScalar1,
                        $oneScalar2,
                    ],
                    'foo' => 'baz',
                ],
                'baz',
                $oneScalar1,
                $oneScalar2,
            ],
        ];
    }

    /**
     * @throws Exception
     */
    public function createScalarMock()
    {
        return $this->createMock(OneScalar::class);
    }
}
