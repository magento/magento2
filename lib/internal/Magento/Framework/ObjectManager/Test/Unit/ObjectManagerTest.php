<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Test\Unit;

use Magento\Framework\ObjectManager\Factory\Dynamic\Developer;

require __DIR__ . '/_files/ChildInterface.php';
require __DIR__ . '/_files/DiParent.php';
require __DIR__ . '/_files/Child.php';
require __DIR__ . '/_files/Child/A.php';
require __DIR__ . '/_files/Child/Circular.php';
require __DIR__ . '/_files/Aggregate/AggregateInterface.php';
require __DIR__ . '/_files/Aggregate/AggregateParent.php';
require __DIR__ . '/_files/Aggregate/Child.php';
require __DIR__ . '/_files/Aggregate/WithOptional.php';

class ObjectManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager
     */
    protected $_object;

    protected function setUp()
    {
        $config = new \Magento\Framework\ObjectManager\Config\Config(
            new \Magento\Framework\ObjectManager\Relations\Runtime()
        );
        $factory = new Developer($config, null, null, [
            'first_param' => 'first_param_value',
            'second_param' => 'second_param_value'
        ]);
        $this->_object = new \Magento\Framework\ObjectManager\ObjectManager($factory, $config);
        $factory->setObjectManager($this->_object);
    }

    public function testCreateCreatesNewInstanceEveryTime()
    {
        $objectA = $this->_object->create(\Magento\Test\Di\Child::class);
        $this->assertInstanceOf(\Magento\Test\Di\Child::class, $objectA);
        $objectB = $this->_object->create(\Magento\Test\Di\Child::class);
        $this->assertInstanceOf(\Magento\Test\Di\Child::class, $objectB);
        $this->assertNotSame($objectA, $objectB);
    }

    public function testGetCreatesNewInstanceOnlyOnce()
    {
        $objectA = $this->_object->get(\Magento\Test\Di\Child::class);
        $this->assertInstanceOf(\Magento\Test\Di\Child::class, $objectA);
        $objectB = $this->_object->get(\Magento\Test\Di\Child::class);
        $this->assertInstanceOf(\Magento\Test\Di\Child::class, $objectB);
        $this->assertSame($objectA, $objectB);
    }

    public function testCreateCreatesPreferredImplementation()
    {
        $this->_object->configure(
            [
                'preferences' => [
                    \Magento\Test\Di\DiInterface::class => \Magento\Test\Di\DiParent::class,
                    \Magento\Test\Di\DiParent::class => \Magento\Test\Di\Child::class,
                ],
            ]
        );
        $interface = $this->_object->create(\Magento\Test\Di\DiInterface::class);
        $parent = $this->_object->create(\Magento\Test\Di\DiParent::class);
        $child = $this->_object->create(\Magento\Test\Di\Child::class);
        $this->assertInstanceOf(\Magento\Test\Di\Child::class, $interface);
        $this->assertInstanceOf(\Magento\Test\Di\Child::class, $parent);
        $this->assertInstanceOf(\Magento\Test\Di\Child::class, $child);
        $this->assertNotSame($interface, $parent);
        $this->assertNotSame($interface, $child);
    }

    public function testGetCreatesPreferredImplementation()
    {
        $this->_object->configure(
            [
                'preferences' => [
                    \Magento\Test\Di\DiInterface::class => \Magento\Test\Di\DiParent::class,
                    \Magento\Test\Di\DiParent::class => \Magento\Test\Di\Child::class,
                ],
            ]
        );
        $interface = $this->_object->get(\Magento\Test\Di\DiInterface::class);
        $parent = $this->_object->get(\Magento\Test\Di\DiParent::class);
        $child = $this->_object->get(\Magento\Test\Di\Child::class);
        $this->assertInstanceOf(\Magento\Test\Di\Child::class, $interface);
        $this->assertInstanceOf(\Magento\Test\Di\Child::class, $parent);
        $this->assertInstanceOf(\Magento\Test\Di\Child::class, $child);
        $this->assertSame($interface, $parent);
        $this->assertSame($interface, $child);
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage Missing required argument $scalar of Magento\Test\Di\Aggregate\AggregateParent
     */
    public function testCreateThrowsExceptionIfRequiredConstructorParameterIsNotProvided()
    {
        $this->_object->configure(
            [
                'preferences' => [
                    \Magento\Test\Di\DiInterface::class => \Magento\Test\Di\DiParent::class,
                    \Magento\Test\Di\DiParent::class => \Magento\Test\Di\Child::class,
                ],
            ]
        );
        $this->_object->create(\Magento\Test\Di\Aggregate\AggregateParent::class);
    }

    public function testCreateResolvesScalarParametersAutomatically()
    {
        $this->_object->configure(
            [
                'preferences' => [
                    \Magento\Test\Di\DiInterface::class => \Magento\Test\Di\DiParent::class,
                    \Magento\Test\Di\DiParent::class => \Magento\Test\Di\Child::class,
                ],
                \Magento\Test\Di\Aggregate\AggregateParent::class => [
                    'arguments' => [
                        'child' => ['instance' => \Magento\Test\Di\Child\A::class],
                        'scalar' => 'scalarValue',
                    ],
                ],
            ]
        );
        /** @var $result \Magento\Test\Di\Aggregate\AggregateParent */
        $result = $this->_object->create(\Magento\Test\Di\Aggregate\AggregateParent::class);
        $this->assertInstanceOf(\Magento\Test\Di\Aggregate\AggregateParent::class, $result);
        $this->assertInstanceOf(\Magento\Test\Di\Child::class, $result->interface);
        $this->assertInstanceOf(\Magento\Test\Di\Child::class, $result->parent);
        $this->assertInstanceOf(\Magento\Test\Di\Child\A::class, $result->child);
        $this->assertEquals('scalarValue', $result->scalar);
        $this->assertEquals('1', $result->optionalScalar);
    }

    public function testGetCreatesSharedInstancesEveryTime()
    {
        $this->_object->configure(
            [
                'preferences' => [
                    \Magento\Test\Di\DiInterface::class => \Magento\Test\Di\DiParent::class,
                    \Magento\Test\Di\DiParent::class => \Magento\Test\Di\Child::class,
                ],
                \Magento\Test\Di\DiInterface::class => ['shared' => 0],
                \Magento\Test\Di\Aggregate\AggregateParent::class => [
                    'arguments' => ['scalar' => 'scalarValue'],
                ],
            ]
        );
        /** @var $result \Magento\Test\Di\Aggregate\AggregateParent */
        $result = $this->_object->create(\Magento\Test\Di\Aggregate\AggregateParent::class);
        $this->assertInstanceOf(\Magento\Test\Di\Aggregate\AggregateParent::class, $result);
        $this->assertInstanceOf(\Magento\Test\Di\Child::class, $result->interface);
        $this->assertInstanceOf(\Magento\Test\Di\Child::class, $result->parent);
        $this->assertInstanceOf(\Magento\Test\Di\Child::class, $result->child);
        $this->assertNotSame($result->interface, $result->parent);
        $this->assertNotSame($result->interface, $result->child);
        $this->assertSame($result->parent, $result->child);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Circular dependency: Magento\Test\Di\Aggregate\AggregateParent depends on
     * Magento\Test\Di\Child\Circular and vice versa.
     */
    public function testGetDetectsCircularDependency()
    {
        $this->_object->configure(
            [
                'preferences' => [
                    \Magento\Test\Di\DiInterface::class => \Magento\Test\Di\DiParent::class,
                    \Magento\Test\Di\DiParent::class => \Magento\Test\Di\Child\Circular::class,
                ],
            ]
        );
        $this->_object->create(\Magento\Test\Di\Aggregate\AggregateParent::class);
    }

    public function testCreateIgnoresOptionalArguments()
    {
        $instance = $this->_object->create(\Magento\Test\Di\Aggregate\WithOptional::class);
        $this->assertNull($instance->parent);
        $this->assertNull($instance->child);
    }

    public function testCreateCreatesPreconfiguredInstance()
    {
        $this->_object->configure(
            [
                'preferences' => [
                    \Magento\Test\Di\DiInterface::class => \Magento\Test\Di\DiParent::class,
                    \Magento\Test\Di\DiParent::class => \Magento\Test\Di\Child::class,
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
                        'interface' => ['instance' => \Magento\Test\Di\DiParent::class],
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
                            'instance' => \Magento\Test\Di\DiParent::class,
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
                        'interface' => ['instance' => \Magento\Test\Di\DiParent::class],
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

        $this->_object->configure([\Magento\Test\Di\DiParent::class => ['shared' => false]]);

        $parent1 = $this->_object->create(\Magento\Test\Di\DiParent::class);
        $parent2 = $this->_object->create(\Magento\Test\Di\DiParent::class);
        $this->assertNotSame($parent1, $parent2);

        $childA = $this->_object->create('customChildType');
        $childB = $this->_object->create('customChildType');
        $this->assertNotSame($childA, $childB);
    }

    public function testParameterShareabilityConfigurationOverridesTypeShareability()
    {
        $this->_object->configure(
            [
                \Magento\Test\Di\DiParent::class => ['shared' => false],
                'customChildType' => [
                    'type' => \Magento\Test\Di\Aggregate\Child::class,
                    'arguments' => [
                        'interface' => ['instance' => \Magento\Test\Di\DiParent::class],
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
                            'instance' => \Magento\Test\Di\DiParent::class,
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
                    \Magento\Test\Di\DiInterface::class => \Magento\Test\Di\DiParent::class
                ],
                \Magento\Test\Di\Aggregate\AggregateParent::class => [
                    'arguments' => [
                        'scalar' => ['argument' => 'first_param'],
                        'optionalScalar' => ['argument' => 'second_param'],
                    ],
                ],
            ]
        );
        /** @var $result \Magento\Test\Di\Aggregate\AggregateParent */
        $result = $this->_object->create(\Magento\Test\Di\Aggregate\AggregateParent::class);
        $this->assertEquals('first_param_value', $result->scalar);
        $this->assertEquals('second_param_value', $result->optionalScalar);
    }

    public function testConfiguredArgumentsAreInherited()
    {
        $this->_object->configure(
            [\Magento\Test\Di\Aggregate\AggregateParent::class => [
                    'arguments' => [
                        'interface' => ['instance' => \Magento\Test\Di\DiParent::class],
                        'scalar' => ['argument' => 'first_param'],
                        'optionalScalar' => 'parentOptionalScalar',
                    ],
                ], \Magento\Test\Di\Aggregate\Child::class => [
                    'arguments' => [
                        'secondScalar' => 'childSecondScalar',
                    ],
                ],
            ]
        );

        /** @var $result \Magento\Test\Di\Aggregate\Child */
        $result = $this->_object->create(\Magento\Test\Di\Aggregate\Child::class);
        $this->assertInstanceOf(\Magento\Test\Di\DiParent::class, $result->interface);
        $this->assertEquals('first_param_value', $result->scalar);
        $this->assertEquals('childSecondScalar', $result->secondScalar);
        $this->assertEquals('parentOptionalScalar', $result->optionalScalar);
    }

    public function testConfiguredArgumentsOverrideInheritedArguments()
    {
        $this->_object->configure(
            [
                \Magento\Test\Di\Aggregate\AggregateParent::class => [
                    'arguments' => [
                        'interface' => ['instance' => \Magento\Test\Di\DiParent::class],
                        'scalar' => ['argument' => 'first_param'],
                        'optionalScalar' => 'parentOptionalScalar',
                    ],
                ],
                \Magento\Test\Di\Aggregate\Child::class => [
                    'arguments' => [
                        'interface' => ['instance' => \Magento\Test\Di\Child::class],
                        'scalar' => ['argument' => 'second_param'],
                        'secondScalar' => 'childSecondScalar',
                        'optionalScalar' => 'childOptionalScalar',
                    ],
                ],
            ]
        );

        /** @var $result \Magento\Test\Di\Aggregate\Child */
        $result = $this->_object->create(\Magento\Test\Di\Aggregate\Child::class);
        $this->assertInstanceOf(\Magento\Test\Di\Child::class, $result->interface);
        $this->assertEquals('second_param_value', $result->scalar);
        $this->assertEquals('childSecondScalar', $result->secondScalar);
        $this->assertEquals('childOptionalScalar', $result->optionalScalar);
    }

    public function testGetIgnoresFirstSlash()
    {
        $this->assertSame(
            $this->_object->get(\Magento\Test\Di\Child::class),
            $this->_object->get('\\' . \Magento\Test\Di\Child::class)
        );
    }
}
