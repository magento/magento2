<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


namespace Magento\Framework\ObjectManager\Test\Unit;

use Magento\Framework\ObjectManager\Config\Config;
use Magento\Framework\ObjectManager\Factory\Dynamic\Developer;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Framework\ObjectManager\Relations\Runtime;
use Magento\Test\Di\Aggregate\AggregateParent;
use Magento\Test\Di\Aggregate\WithOptional;
use Magento\Test\Di\Child;
use Magento\Test\Di\Child\A;
use Magento\Test\Di\Child\Circular;
use Magento\Test\Di\DiInterface;
use Magento\Test\Di\DiParent;
use PHPUnit\Framework\TestCase;

require __DIR__ . '/_files/ChildInterface.php';
require __DIR__ . '/_files/DiParent.php';
require __DIR__ . '/_files/Child.php';
require __DIR__ . '/_files/Child/A.php';
require __DIR__ . '/_files/Child/Circular.php';
require __DIR__ . '/_files/Aggregate/AggregateInterface.php';
require __DIR__ . '/_files/Aggregate/AggregateParent.php';
require __DIR__ . '/_files/Aggregate/Child.php';
require __DIR__ . '/_files/Aggregate/WithOptional.php';

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ObjectManagerTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $_object;

    protected function setUp(): void
    {
        $config = new Config(
            new Runtime()
        );
        $factory = new Developer(
            $config,
            null,
            null,
            [
                'first_param' => 'first_param_value',
                'second_param' => 'second_param_value'
            ]
        );
        $this->_object = new ObjectManager($factory, $config);
        $factory->setObjectManager($this->_object);
    }

    public function testCreateCreatesNewInstanceEveryTime()
    {
        $objectA = $this->_object->create(Child::class);
        $this->assertInstanceOf(Child::class, $objectA);
        $objectB = $this->_object->create(Child::class);
        $this->assertInstanceOf(Child::class, $objectB);
        $this->assertNotSame($objectA, $objectB);
    }

    public function testGetCreatesNewInstanceOnlyOnce()
    {
        $objectA = $this->_object->get(Child::class);
        $this->assertInstanceOf(Child::class, $objectA);
        $objectB = $this->_object->get(Child::class);
        $this->assertInstanceOf(Child::class, $objectB);
        $this->assertSame($objectA, $objectB);
    }

    public function testCreateCreatesPreferredImplementation()
    {
        $this->_object->configure(
            [
                'preferences' => [
                    DiInterface::class => DiParent::class,
                    DiParent::class => Child::class,
                ],
            ]
        );
        $interface = $this->_object->create(DiInterface::class);
        $parent = $this->_object->create(DiParent::class);
        $child = $this->_object->create(Child::class);
        $this->assertInstanceOf(Child::class, $interface);
        $this->assertInstanceOf(Child::class, $parent);
        $this->assertInstanceOf(Child::class, $child);
        $this->assertNotSame($interface, $parent);
        $this->assertNotSame($interface, $child);
    }

    public function testGetCreatesPreferredImplementation()
    {
        $this->_object->configure(
            [
                'preferences' => [
                    DiInterface::class => DiParent::class,
                    DiParent::class => Child::class,
                ],
            ]
        );
        $interface = $this->_object->get(DiInterface::class);
        $parent = $this->_object->get(DiParent::class);
        $child = $this->_object->get(Child::class);
        $this->assertInstanceOf(Child::class, $interface);
        $this->assertInstanceOf(Child::class, $parent);
        $this->assertInstanceOf(Child::class, $child);
        $this->assertSame($interface, $parent);
        $this->assertSame($interface, $child);
    }

    public function testCreateThrowsExceptionIfRequiredConstructorParameterIsNotProvided()
    {
        $this->expectException('BadMethodCallException');
        $this->expectExceptionMessage('Missing required argument $scalar of Magento\Test\Di\Aggregate\AggregateParent');
        $this->_object->configure(
            [
                'preferences' => [
                    DiInterface::class => DiParent::class,
                    DiParent::class => Child::class,
                ],
            ]
        );
        $this->_object->create(AggregateParent::class);
    }

    public function testCreateResolvesScalarParametersAutomatically()
    {
        $this->_object->configure(
            [
                'preferences' => [
                    DiInterface::class => DiParent::class,
                    DiParent::class => Child::class,
                ],
                AggregateParent::class => [
                    'arguments' => [
                        'child' => ['instance' => A::class],
                        'scalar' => 'scalarValue',
                    ],
                ],
            ]
        );
        /** @var $result \Magento\Test\Di\Aggregate\AggregateParent */
        $result = $this->_object->create(AggregateParent::class);
        $this->assertInstanceOf(AggregateParent::class, $result);
        $this->assertInstanceOf(Child::class, $result->interface);
        $this->assertInstanceOf(Child::class, $result->parent);
        $this->assertInstanceOf(A::class, $result->child);
        $this->assertEquals('scalarValue', $result->scalar);
        $this->assertEquals('1', $result->optionalScalar);
    }

    public function testGetCreatesSharedInstancesEveryTime()
    {
        $this->_object->configure(
            [
                'preferences' => [
                    DiInterface::class => DiParent::class,
                    DiParent::class => Child::class,
                ],
                DiInterface::class => ['shared' => 0],
                AggregateParent::class => [
                    'arguments' => ['scalar' => 'scalarValue'],
                ],
            ]
        );
        /** @var $result \Magento\Test\Di\Aggregate\AggregateParent */
        $result = $this->_object->create(AggregateParent::class);
        $this->assertInstanceOf(AggregateParent::class, $result);
        $this->assertInstanceOf(Child::class, $result->interface);
        $this->assertInstanceOf(Child::class, $result->parent);
        $this->assertInstanceOf(Child::class, $result->child);
        $this->assertNotSame($result->interface, $result->parent);
        $this->assertNotSame($result->interface, $result->child);
        $this->assertSame($result->parent, $result->child);
    }

    public function testGetDetectsCircularDependency()
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessage(
            'Circular dependency: Magento\Test\Di\Aggregate\AggregateParent'
            . ' depends on Magento\Test\Di\Child\Circular and vice versa.'
        );
        $this->_object->configure(
            [
                'preferences' => [
                    DiInterface::class => DiParent::class,
                    DiParent::class => Circular::class,
                ],
            ]
        );
        $this->_object->create(AggregateParent::class);
    }

    public function testCreateIgnoresOptionalArguments()
    {
        $instance = $this->_object->create(WithOptional::class);
        $this->assertNull($instance->parent);
        $this->assertNull($instance->child);
    }

    public function testCreateCreatesPreconfiguredInstance()
    {
        $this->_object->configure(
            [
                'preferences' => [
                    DiInterface::class => DiParent::class,
                    DiParent::class => Child::class,
                ],
                'customChildType' => [
                    'type' => \Magento\Test\Di\Aggregate\Child::class,
                    'arguments' => [
                        'scalar' => 'configuredScalar',
                        'secondScalar' => 'configuredSecondScalar',
                        'secondOptionalScalar' => 'configuredOptionalScalar',
                    ],
                ],
            ]
        );
        $customChild = $this->_object->get('customChildType');
        $this->assertInstanceOf(\Magento\Test\Di\Aggregate\Child::class, $customChild);
        $this->assertEquals('configuredScalar', $customChild->scalar);
        $this->assertEquals('configuredSecondScalar', $customChild->secondScalar);
        $this->assertEquals(1, $customChild->optionalScalar);
        $this->assertEquals('configuredOptionalScalar', $customChild->secondOptionalScalar);
        $this->assertSame($customChild, $this->_object->get('customChildType'));
    }

    public function testParameterShareabilityConfigurationIsApplied()
    {
        $this->_object->configure(
            [
                'customChildType' => [
                    'type' => \Magento\Test\Di\Aggregate\Child::class,
                    'arguments' => [
                        'interface' => ['instance' => DiParent::class],
                        'scalar' => 'configuredScalar',
                        'secondScalar' => 'configuredSecondScalar',
                    ],
                ],
            ]
        );
        $childA = $this->_object->create('customChildType');
        $childB = $this->_object->create('customChildType');
        $this->assertNotSame($childA, $childB);
        $this->assertSame($childA->interface, $childB->interface);

        $this->_object->configure(
            [
                'customChildType' => [
                    'arguments' => [
                        'interface' => [
                            'instance' => DiParent::class,
                            'shared' => false,
                        ],
                    ],
                ],
            ]
        );
        $childA = $this->_object->create('customChildType');
        $childB = $this->_object->create('customChildType');
        $this->assertNotSame($childA, $childB);
        $this->assertNotSame($childA->interface, $childB->interface);
    }

    public function testTypeShareabilityConfigurationIsApplied()
    {
        $this->_object->configure(
            [
                'customChildType' => [
                    'type' => \Magento\Test\Di\Aggregate\Child::class,
                    'arguments' => [
                        'interface' => ['instance' => DiParent::class],
                        'scalar' => 'configuredScalar',
                        'secondScalar' => 'configuredSecondScalar',
                    ],
                ],
            ]
        );
        $childA = $this->_object->create('customChildType');
        $childB = $this->_object->create('customChildType');
        $this->assertNotSame($childA, $childB);
        $this->assertSame($childA->interface, $childB->interface);

        $this->_object->configure([DiParent::class => ['shared' => false]]);

        $parent1 = $this->_object->create(DiParent::class);
        $parent2 = $this->_object->create(DiParent::class);
        $this->assertNotSame($parent1, $parent2);

        $childA = $this->_object->create('customChildType');
        $childB = $this->_object->create('customChildType');
        $this->assertNotSame($childA, $childB);
    }

    public function testParameterShareabilityConfigurationOverridesTypeShareability()
    {
        $this->_object->configure(
            [
                DiParent::class => ['shared' => false],
                'customChildType' => [
                    'type' => \Magento\Test\Di\Aggregate\Child::class,
                    'arguments' => [
                        'interface' => ['instance' => DiParent::class],
                        'scalar' => 'configuredScalar',
                        'secondScalar' => 'configuredSecondScalar',
                    ],
                ],
            ]
        );
        $childA = $this->_object->create('customChildType');
        $childB = $this->_object->create('customChildType');
        $this->assertNotSame($childA, $childB);
        $this->assertNotSame($childA->interface, $childB->interface);

        $this->_object->configure(
            [
                'customChildType' => [
                    'arguments' => [
                        'interface' => [
                            'instance' => DiParent::class,
                            'shared' => true,
                        ],
                    ],
                ],
            ]
        );
        $childA = $this->_object->create('customChildType');
        $childB = $this->_object->create('customChildType');
        $this->assertNotSame($childA, $childB);
        $this->assertSame($childA->interface, $childB->interface);
    }

    public function testGlobalArgumentsCanBeConfigured()
    {
        $this->_object->configure(
            [
                'preferences' => [
                    DiInterface::class => DiParent::class
                ],
                AggregateParent::class => [
                    'arguments' => [
                        'scalar' => ['argument' => 'first_param'],
                        'optionalScalar' => ['argument' => 'second_param'],
                    ],
                ],
            ]
        );
        /** @var $result \Magento\Test\Di\Aggregate\AggregateParent */
        $result = $this->_object->create(AggregateParent::class);
        $this->assertEquals('first_param_value', $result->scalar);
        $this->assertEquals('second_param_value', $result->optionalScalar);
    }

    public function testConfiguredArgumentsAreInherited()
    {
        $this->_object->configure(
            [
                AggregateParent::class => [
                    'arguments' => [
                        'interface' => ['instance' => DiParent::class],
                        'scalar' => ['argument' => 'first_param'],
                        'optionalScalar' => 'parentOptionalScalar',
                    ],
                ],
                \Magento\Test\Di\Aggregate\Child::class => [
                    'arguments' => [
                        'secondScalar' => 'childSecondScalar',
                    ],
                ],
            ]
        );

        /** @var $result \Magento\Test\Di\Aggregate\Child */
        $result = $this->_object->create(\Magento\Test\Di\Aggregate\Child::class);
        $this->assertInstanceOf(DiParent::class, $result->interface);
        $this->assertEquals('first_param_value', $result->scalar);
        $this->assertEquals('childSecondScalar', $result->secondScalar);
        $this->assertEquals('parentOptionalScalar', $result->optionalScalar);
    }

    public function testConfiguredArgumentsOverrideInheritedArguments()
    {
        $this->_object->configure(
            [
                AggregateParent::class => [
                    'arguments' => [
                        'interface' => ['instance' => DiParent::class],
                        'scalar' => ['argument' => 'first_param'],
                        'optionalScalar' => 'parentOptionalScalar',
                    ],
                ],
                \Magento\Test\Di\Aggregate\Child::class => [
                    'arguments' => [
                        'interface' => ['instance' => Child::class],
                        'scalar' => ['argument' => 'second_param'],
                        'secondScalar' => 'childSecondScalar',
                        'optionalScalar' => 'childOptionalScalar',
                    ],
                ],
            ]
        );

        /** @var $result \Magento\Test\Di\Aggregate\Child */
        $result = $this->_object->create(\Magento\Test\Di\Aggregate\Child::class);
        $this->assertInstanceOf(Child::class, $result->interface);
        $this->assertEquals('second_param_value', $result->scalar);
        $this->assertEquals('childSecondScalar', $result->secondScalar);
        $this->assertEquals('childOptionalScalar', $result->optionalScalar);
    }

    public function testGetIgnoresFirstSlash()
    {
        $this->assertSame(
            $this->_object->get(Child::class),
            $this->_object->get('\\' . Child::class)
        );
    }
}
