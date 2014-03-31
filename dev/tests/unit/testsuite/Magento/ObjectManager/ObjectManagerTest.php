<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\ObjectManager;


require __DIR__ . '/../_files/ChildInterface.php';
require __DIR__ . '/../_files/DiParent.php';
require __DIR__ . '/../_files/Child.php';
require __DIR__ . '/../_files/Child/A.php';
require __DIR__ . '/../_files/Child/Circular.php';
require __DIR__ . '/../_files/Aggregate/AggregateInterface.php';
require __DIR__ . '/../_files/Aggregate/AggregateParent.php';
require __DIR__ . '/../_files/Aggregate/Child.php';
require __DIR__ . '/../_files/Aggregate/WithOptional.php';
class ObjectManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ObjectManager\ObjectManager
     */
    protected $_object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_argInterpreterMock;

    protected function setUp()
    {
        $this->_argInterpreterMock = $this->getMock(
            '\Magento\Data\Argument\InterpreterInterface',
            array(),
            array(),
            '',
            false
        );
        $config = new \Magento\ObjectManager\Config\Config(new \Magento\ObjectManager\Relations\Runtime());
        $argObjectFactory = new \Magento\ObjectManager\Config\Argument\ObjectFactory($config);
        $factory = new \Magento\ObjectManager\Factory\Factory(
            $config,
            $this->_argInterpreterMock,
            $argObjectFactory,
            null
        );
        $this->_object = new \Magento\ObjectManager\ObjectManager($factory, $config);
        $argObjectFactory->setObjectManager($this->_object);
    }

    public function testCreateCreatesNewInstanceEveryTime()
    {
        $objectA = $this->_object->create('Magento\Test\Di\Child');
        $this->assertInstanceOf('Magento\Test\Di\Child', $objectA);
        $objectB = $this->_object->create('Magento\Test\Di\Child');
        $this->assertInstanceOf('Magento\Test\Di\Child', $objectB);
        $this->assertNotSame($objectA, $objectB);
    }

    public function testGetCreatesNewInstanceOnlyOnce()
    {
        $objectA = $this->_object->get('Magento\Test\Di\Child');
        $this->assertInstanceOf('Magento\Test\Di\Child', $objectA);
        $objectB = $this->_object->get('Magento\Test\Di\Child');
        $this->assertInstanceOf('Magento\Test\Di\Child', $objectB);
        $this->assertSame($objectA, $objectB);
    }

    public function testCreateCreatesPreferredImplementation()
    {
        $this->_object->configure(
            array(
                'preferences' => array(
                    'Magento\Test\Di\DiInterface' => 'Magento\Test\Di\DiParent',
                    'Magento\Test\Di\DiParent' => 'Magento\Test\Di\Child'
                )
            )
        );
        $interface = $this->_object->create('Magento\Test\Di\DiInterface');
        $parent = $this->_object->create('Magento\Test\Di\DiParent');
        $child = $this->_object->create('Magento\Test\Di\Child');
        $this->assertInstanceOf('Magento\Test\Di\Child', $interface);
        $this->assertInstanceOf('Magento\Test\Di\Child', $parent);
        $this->assertInstanceOf('Magento\Test\Di\Child', $child);
        $this->assertNotSame($interface, $parent);
        $this->assertNotSame($interface, $child);
    }

    public function testGetCreatesPreferredImplementation()
    {
        $this->_object->configure(
            array(
                'preferences' => array(
                    'Magento\Test\Di\DiInterface' => 'Magento\Test\Di\DiParent',
                    'Magento\Test\Di\DiParent' => 'Magento\Test\Di\Child'
                )
            )
        );
        $interface = $this->_object->get('Magento\Test\Di\DiInterface');
        $parent = $this->_object->get('Magento\Test\Di\DiParent');
        $child = $this->_object->get('Magento\Test\Di\Child');
        $this->assertInstanceOf('Magento\Test\Di\Child', $interface);
        $this->assertInstanceOf('Magento\Test\Di\Child', $parent);
        $this->assertInstanceOf('Magento\Test\Di\Child', $child);
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
            array(
                'preferences' => array(
                    'Magento\Test\Di\DiInterface' => 'Magento\Test\Di\DiParent',
                    'Magento\Test\Di\DiParent' => 'Magento\Test\Di\Child'
                )
            )
        );
        $this->_object->create('Magento\Test\Di\Aggregate\AggregateParent');
    }

    public function testCreateResolvesScalarParametersAutomatically()
    {
        $childAMock = $this->getMock('Magento\Test\Di\Child\A', array(), array(), '', false);
        $this->_argInterpreterMock->expects(
            $this->any()
        )->method(
            'evaluate'
        )->will(
            $this->returnValueMap(
                array(
                    array(array('xsi:type' => 'object', 'value' => 'Magento\Test\Di\Child\A'), $childAMock),
                    array(array('xsi:type' => 'string', 'value' => 'scalarValue'), 'scalarValue')
                )
            )
        );

        $this->_object->configure(
            array(
                'preferences' => array(
                    'Magento\Test\Di\DiInterface' => 'Magento\Test\Di\DiParent',
                    'Magento\Test\Di\DiParent' => 'Magento\Test\Di\Child'
                ),
                'Magento\Test\Di\Aggregate\AggregateParent' => array(
                    'arguments' => array(
                        'child' => array('xsi:type' => 'object', 'value' => 'Magento\Test\Di\Child\A'),
                        'scalar' => array('xsi:type' => 'string', 'value' => 'scalarValue')
                    )
                )
            )
        );
        /** @var $result \Magento\Test\Di\Aggregate\AggregateParent */
        $result = $this->_object->create('Magento\Test\Di\Aggregate\AggregateParent');
        $this->assertInstanceOf('Magento\Test\Di\Aggregate\AggregateParent', $result);
        $this->assertInstanceOf('Magento\Test\Di\Child', $result->interface);
        $this->assertInstanceOf('Magento\Test\Di\Child', $result->parent);
        $this->assertInstanceOf('Magento\Test\Di\Child\A', $result->child);
        $this->assertEquals('scalarValue', $result->scalar);
        $this->assertEquals('1', $result->optionalScalar);
    }

    public function testGetCreatesSharedInstancesEveryTime()
    {
        $this->_object->configure(
            array(
                'preferences' => array(
                    'Magento\Test\Di\DiInterface' => 'Magento\Test\Di\DiParent',
                    'Magento\Test\Di\DiParent' => 'Magento\Test\Di\Child'
                ),
                'Magento\Test\Di\DiInterface' => array('shared' => 0),
                'Magento\Test\Di\Aggregate\AggregateParent' => array(
                    'arguments' => array('scalar' => array('xsi:type' => 'string', 'value' => 'scalarValue'))
                )
            )
        );
        /** @var $result \Magento\Test\Di\Aggregate\AggregateParent */
        $result = $this->_object->create('Magento\Test\Di\Aggregate\AggregateParent');
        $this->assertInstanceOf('Magento\Test\Di\Aggregate\AggregateParent', $result);
        $this->assertInstanceOf('Magento\Test\Di\Child', $result->interface);
        $this->assertInstanceOf('Magento\Test\Di\Child', $result->parent);
        $this->assertInstanceOf('Magento\Test\Di\Child', $result->child);
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
            array(
                'preferences' => array(
                    'Magento\Test\Di\DiInterface' => 'Magento\Test\Di\DiParent',
                    'Magento\Test\Di\DiParent' => 'Magento\Test\Di\Child\Circular'
                )
            )
        );
        $this->_object->create('Magento\Test\Di\Aggregate\AggregateParent');
    }

    public function testCreateIgnoresOptionalArguments()
    {
        $instance = $this->_object->create('Magento\Test\Di\Aggregate\WithOptional');
        $this->assertNull($instance->parent);
        $this->assertNull($instance->child);
    }

    public function testCreateCreatesPreconfiguredInstance()
    {
        $this->_argInterpreterMock->expects(
            $this->any()
        )->method(
            'evaluate'
        )->will(
            $this->returnValueMap(
                array(
                    array(array('xsi:type' => 'string', 'value' => 'configuredScalar'), 'configuredScalar'),
                    array(
                        array('xsi:type' => 'string', 'value' => 'configuredSecondScalar'),
                        'configuredSecondScalar'
                    ),
                    array(
                        array('xsi:type' => 'string', 'value' => 'configuredOptionalScalar'),
                        'configuredOptionalScalar'
                    )
                )
            )
        );

        $this->_object->configure(
            array(
                'preferences' => array(
                    'Magento\Test\Di\DiInterface' => 'Magento\Test\Di\DiParent',
                    'Magento\Test\Di\DiParent' => 'Magento\Test\Di\Child'
                ),
                'customChildType' => array(
                    'type' => 'Magento\Test\Di\Aggregate\Child',
                    'arguments' => array(
                        'scalar' => array('xsi:type' => 'string', 'value' => 'configuredScalar'),
                        'secondScalar' => array('xsi:type' => 'string', 'value' => 'configuredSecondScalar'),
                        'secondOptionalScalar' => array('xsi:type' => 'string', 'value' => 'configuredOptionalScalar')
                    )
                )
            )
        );
        $customChild = $this->_object->get('customChildType');
        $this->assertInstanceOf('Magento\Test\Di\Aggregate\Child', $customChild);
        $this->assertEquals('configuredScalar', $customChild->scalar);
        $this->assertEquals('configuredSecondScalar', $customChild->secondScalar);
        $this->assertEquals(1, $customChild->optionalScalar);
        $this->assertEquals('configuredOptionalScalar', $customChild->secondOptionalScalar);
        $this->assertSame($customChild, $this->_object->get('customChildType'));
    }

    public function testParameterShareabilityConfigurationIsApplied()
    {
        $diParentMock = $this->getMock('Magento\Test\Di\DiParent', array(), array(), '', false);
        $this->_argInterpreterMock->expects($this->any())->method('evaluate')->will(
            $this->returnCallback(
                function (array $array) use ($diParentMock) {
                    if ($array === array('xsi:type' => 'object', 'value' => 'Magento\Test\Di\DiParent')) {
                        return $diParentMock;
                    } elseif ($array === array(
                        'xsi:type' => 'object',
                        'value' => 'Magento\Test\Di\DiParent',
                        'shared' => false
                    )
                    ) {
                        return $this->getMock('Magento\Test\Di\DiParent', array(), array(), '', false);
                    }
                }
            )
        );

        $this->_object->configure(
            array(
                'customChildType' => array(
                    'type' => 'Magento\Test\Di\Aggregate\Child',
                    'arguments' => array(
                        'interface' => array('xsi:type' => 'object', 'value' => 'Magento\Test\Di\DiParent'),
                        'scalar' => array('xsi:type' => 'string', 'value' => 'configuredScalar'),
                        'secondScalar' => array('xsi:type' => 'string', 'value' => 'configuredSecondScalar')
                    )
                )
            )
        );
        $childA = $this->_object->create('customChildType');
        $childB = $this->_object->create('customChildType');
        $this->assertNotSame($childA, $childB);
        $this->assertSame($childA->interface, $childB->interface);

        $this->_object->configure(
            array(
                'customChildType' => array(
                    'arguments' => array(
                        'interface' => array(
                            'xsi:type' => 'object',
                            'value' => 'Magento\Test\Di\DiParent',
                            'shared' => false
                        )
                    )
                )
            )
        );
        $childA = $this->_object->create('customChildType');
        $childB = $this->_object->create('customChildType');
        $this->assertNotSame($childA, $childB);
        $this->assertNotSame($childA->interface, $childB->interface);
    }

    public function testTypeShareabilityConfigurationIsApplied()
    {
        $diParentMock = $this->getMock('Magento\Test\Di\DiParent', array(), array(), '', false);
        $this->_argInterpreterMock->expects(
            $this->any()
        )->method(
            'evaluate'
        )->will(
            $this->returnValueMap(
                array(array(array('xsi:type' => 'object', 'value' => 'Magento\Test\Di\DiParent'), $diParentMock))
            )
        );

        $this->_object->configure(
            array(
                'customChildType' => array(
                    'type' => 'Magento\Test\Di\Aggregate\Child',
                    'arguments' => array(
                        'interface' => array('xsi:type' => 'object', 'value' => 'Magento\Test\Di\DiParent'),
                        'scalar' => array('xsi:type' => 'string', 'value' => 'configuredScalar'),
                        'secondScalar' => array('xsi:type' => 'string', 'value' => 'configuredSecondScalar')
                    )
                )
            )
        );
        $childA = $this->_object->create('customChildType');
        $childB = $this->_object->create('customChildType');
        $this->assertNotSame($childA, $childB);
        $this->assertSame($childA->interface, $childB->interface);

        $this->_object->configure(array('Magento\Test\Di\DiParent' => array('shared' => false)));

        $parent1 = $this->_object->create('Magento\Test\Di\DiParent');
        $parent2 = $this->_object->create('Magento\Test\Di\DiParent');
        $this->assertNotSame($parent1, $parent2);

        $childA = $this->_object->create('customChildType');
        $childB = $this->_object->create('customChildType');
        $this->assertNotSame($childA, $childB);
    }

    public function testParameterShareabilityConfigurationOverridesTypeShareability()
    {
        $diParentMock = $this->getMock('Magento\Test\Di\DiParent', array(), array(), '', false);
        $this->_argInterpreterMock->expects($this->any())->method('evaluate')->will(
            $this->returnCallback(
                function (array $array) use ($diParentMock) {
                    if ($array === array('xsi:type' => 'object', 'value' => 'Magento\Test\Di\DiParent')) {
                        return $this->_object->create('Magento\Test\Di\DiParent');
                    } elseif ($array === array(
                        'xsi:type' => 'object',
                        'value' => 'Magento\Test\Di\DiParent',
                        'shared' => true
                    )
                    ) {
                        return $diParentMock;
                    }
                }
            )
        );

        $this->_object->configure(
            array(
                'Magento\Test\Di\DiParent' => array('shared' => false),
                'customChildType' => array(
                    'type' => 'Magento\Test\Di\Aggregate\Child',
                    'arguments' => array(
                        'interface' => array('xsi:type' => 'object', 'value' => 'Magento\Test\Di\DiParent'),
                        'scalar' => array('xsi:type' => 'string', 'value' => 'configuredScalar'),
                        'secondScalar' => array('xsi:type' => 'string', 'value' => 'configuredSecondScalar')
                    )
                )
            )
        );
        $childA = $this->_object->create('customChildType');
        $childB = $this->_object->create('customChildType');
        $this->assertNotSame($childA, $childB);
        $this->assertNotSame($childA->interface, $childB->interface);

        $this->_object->configure(
            array(
                'customChildType' => array(
                    'arguments' => array(
                        'interface' => array(
                            'xsi:type' => 'object',
                            'value' => 'Magento\Test\Di\DiParent',
                            'shared' => true
                        )
                    )
                )
            )
        );
        $childA = $this->_object->create('customChildType');
        $childB = $this->_object->create('customChildType');
        $this->assertNotSame($childA, $childB);
        $this->assertSame($childA->interface, $childB->interface);
    }

    public function testGlobalArgumentsCanBeConfigured()
    {
        $this->_argInterpreterMock->expects(
            $this->any()
        )->method(
            'evaluate'
        )->will(
            $this->returnValueMap(
                array(
                    array(array('xsi:type' => 'init_parameter', 'value' => 'one'), 'first_val'),
                    array(array('xsi:type' => 'init_parameter', 'value' => 'two'), 'second_val')
                )
            )
        );

        $this->_object->configure(
            array(
                'preferences' => array('Magento\Test\Di\DiInterface' => 'Magento\Test\Di\DiParent'),
                'Magento\Test\Di\Aggregate\AggregateParent' => array(
                    'arguments' => array(
                        'scalar' => array('xsi:type' => 'init_parameter', 'value' => 'one'),
                        'optionalScalar' => array('xsi:type' => 'init_parameter', 'value' => 'two')
                    )
                )
            )
        );
        /** @var $result \Magento\Test\Di\Aggregate\AggregateParent */
        $result = $this->_object->create('Magento\Test\Di\Aggregate\AggregateParent');
        $this->assertEquals('first_val', $result->scalar);
        $this->assertEquals('second_val', $result->optionalScalar);
    }

    public function testConfiguredArgumentsAreInherited()
    {
        $diParentMock = $this->getMock('Magento\Test\Di\DiParent', array(), array(), '', false);
        $this->_argInterpreterMock->expects(
            $this->any()
        )->method(
            'evaluate'
        )->will(
            $this->returnValueMap(
                array(
                    array(array('xsi:type' => 'init_parameter', 'value' => 'one'), 'first_val'),
                    array(array('xsi:type' => 'object', 'value' => 'Magento\Test\Di\DiParent'), $diParentMock),
                    array(array('xsi:type' => 'string', 'value' => 'parentOptionalScalar'), 'parentOptionalScalar'),
                    array(array('xsi:type' => 'string', 'value' => 'childSecondScalar'), 'childSecondScalar')
                )
            )
        );

        $this->_object->configure(
            array(
                'Magento\Test\Di\Aggregate\AggregateParent' => array(
                    'arguments' => array(
                        'interface' => array('xsi:type' => 'object', 'value' => 'Magento\Test\Di\DiParent'),
                        'scalar' => array('xsi:type' => 'init_parameter', 'value' => 'one'),
                        'optionalScalar' => array('xsi:type' => 'string', 'value' => 'parentOptionalScalar')
                    )
                ),
                'Magento\Test\Di\Aggregate\Child' => array(
                    'arguments' => array(
                        'secondScalar' => array('xsi:type' => 'string', 'value' => 'childSecondScalar')
                    )
                )
            )
        );

        /** @var $result \Magento\Test\Di\Aggregate\AggregateParent */
        $result = $this->_object->create('Magento\Test\Di\Aggregate\Child');
        $this->assertInstanceOf('Magento\Test\Di\DiParent', $result->interface);
        $this->assertEquals('first_val', $result->scalar);
        $this->assertEquals('childSecondScalar', $result->secondScalar);
        $this->assertEquals('parentOptionalScalar', $result->optionalScalar);
    }

    public function testConfiguredArgumentsOverrideInheritedArguments()
    {
        $diChildMock = $this->getMock('Magento\Test\Di\Child', array(), array(), '', false);
        $this->_argInterpreterMock->expects(
            $this->any()
        )->method(
            'evaluate'
        )->will(
            $this->returnValueMap(
                array(
                    array(array('xsi:type' => 'object', 'value' => 'Magento\Test\Di\Child'), $diChildMock),
                    array(array('xsi:type' => 'init_parameter', 'value' => 'two'), 'second_val'),
                    array(array('xsi:type' => 'string', 'value' => 'childSecondScalar'), 'childSecondScalar'),
                    array(array('xsi:type' => 'string', 'value' => 'childOptionalScalar'), 'childOptionalScalar')
                )
            )
        );

        $this->_object->configure(
            array(
                'Magento\Test\Di\Aggregate\AggregateParent' => array(
                    'arguments' => array(
                        'interface' => array('xsi:type' => 'object', 'value' => 'Magento\Test\Di\DiParent'),
                        'scalar' => array('xsi:type' => 'init_parameter', 'value' => 'one'),
                        'optionalScalar' => array('xsi:type' => 'string', 'value' => 'parentOptionalScalar')
                    )
                ),
                'Magento\Test\Di\Aggregate\Child' => array(
                    'arguments' => array(
                        'interface' => array('xsi:type' => 'object', 'value' => 'Magento\Test\Di\Child'),
                        'scalar' => array('xsi:type' => 'init_parameter', 'value' => 'two'),
                        'secondScalar' => array('xsi:type' => 'string', 'value' => 'childSecondScalar'),
                        'optionalScalar' => array('xsi:type' => 'string', 'value' => 'childOptionalScalar')
                    )
                )
            )
        );

        /** @var $result \Magento\Test\Di\Aggregate\AggregateParent */
        $result = $this->_object->create('Magento\Test\Di\Aggregate\Child');
        $this->assertInstanceOf('Magento\Test\Di\Child', $result->interface);
        $this->assertEquals('second_val', $result->scalar);
        $this->assertEquals('childSecondScalar', $result->secondScalar);
        $this->assertEquals('childOptionalScalar', $result->optionalScalar);
    }

    public function testGetIgnoresFirstSlash()
    {
        $this->assertSame($this->_object->get('Magento\Test\Di\Child'), $this->_object->get('\Magento\Test\Di\Child'));
    }
}
