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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
require __DIR__ . '/../_files/Interface.php';
require __DIR__ . '/../_files/Parent.php';
require __DIR__ . '/../_files/Child.php';
require __DIR__ . '/../_files/Child/A.php';
require __DIR__ . '/../_files/Child/Circular.php';
require __DIR__ . '/../_files/Aggregate/Interface.php';
require __DIR__ . '/../_files/Aggregate/Parent.php';
require __DIR__ . '/../_files/Aggregate/Child.php';
require __DIR__ . '/../_files/Aggregate/WithOptional.php';

class Magento_ObjectManager_ObjectManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_ObjectManager_ObjectManager
     */
    protected $_object;

    protected function setUp()
    {
        $definitions = new Magento_ObjectManager_Definition_Runtime();
        $this->_object = new Magento_ObjectManager_ObjectManager(
            $definitions
        );
    }

    public function testCreateCreatesNewInstanceEveryTime()
    {
        $object1 = $this->_object->create('Magento_Test_Di_Child');
        $this->assertInstanceOf('Magento_Test_Di_Child', $object1);
        $object2 = $this->_object->create('Magento_Test_Di_Child');
        $this->assertInstanceOf('Magento_Test_Di_Child', $object2);
        $this->assertNotSame($object1, $object2);
    }

    public function testGetCreatesNewInstanceOnlyOnce()
    {
        $object1 = $this->_object->get('Magento_Test_Di_Child');
        $this->assertInstanceOf('Magento_Test_Di_Child', $object1);
        $object2 = $this->_object->get('Magento_Test_Di_Child');
        $this->assertInstanceOf('Magento_Test_Di_Child', $object2);
        $this->assertSame($object1, $object2);
    }

    public function testCreateCreatesPreferredImplementation()
    {
        $this->_object->configure(array(
            'preferences' => array(
                'Magento_Test_Di_Interface' => 'Magento_Test_Di_Parent',
                'Magento_Test_Di_Parent' => 'Magento_Test_Di_Child'
            )
        ));
        $interface = $this->_object->create('Magento_Test_Di_Interface');
        $parent = $this->_object->create('Magento_Test_Di_Parent');
        $child = $this->_object->create('Magento_Test_Di_Child');
        $this->assertInstanceOf('Magento_Test_Di_Child', $interface);
        $this->assertInstanceOf('Magento_Test_Di_Child', $parent);
        $this->assertInstanceOf('Magento_Test_Di_Child', $child);
        $this->assertNotSame($interface, $parent);
        $this->assertNotSame($interface, $child);
    }

    public function testGetCreatesPreferredImplementation()
    {
        $this->_object->configure(array(
            'preferences' => array(
                'Magento_Test_Di_Interface' => 'Magento_Test_Di_Parent',
                'Magento_Test_Di_Parent' => 'Magento_Test_Di_Child'
            )
        ));
        $interface = $this->_object->get('Magento_Test_Di_Interface');
        $parent = $this->_object->get('Magento_Test_Di_Parent');
        $child = $this->_object->get('Magento_Test_Di_Child');
        $this->assertInstanceOf('Magento_Test_Di_Child', $interface);
        $this->assertInstanceOf('Magento_Test_Di_Child', $parent);
        $this->assertInstanceOf('Magento_Test_Di_Child', $child);
        $this->assertSame($interface, $parent);
        $this->assertSame($interface, $child);
    }

    /**
     * @expectedException BadMethodCallException
     * @expectedExceptionMessage Missing required argument $scalar for Magento_Test_Di_Aggregate_Parent
     */
    public function testCreateThrowsExceptionIfRequiredConstructorParameterIsNotProvided()
    {
        $this->_object->configure(array(
            'preferences' => array(
                'Magento_Test_Di_Interface' => 'Magento_Test_Di_Parent',
                'Magento_Test_Di_Parent' => 'Magento_Test_Di_Child'
            )
        ));
        $this->_object->create('Magento_Test_Di_Aggregate_Parent');
    }

    public function testCreateResolvesScalarParametersAutomatically()
    {
        $this->_object->configure(array(
            'preferences' => array(
                'Magento_Test_Di_Interface' => 'Magento_Test_Di_Parent',
                'Magento_Test_Di_Parent' => 'Magento_Test_Di_Child'
            ),
            'Magento_Test_Di_Aggregate_Parent' => array(
                'parameters' => array(
                    'child' => 'Magento_Test_Di_Child_A',
                    'scalar' => 'scalarValue'
                )
            )
        ));
        /** @var $result Magento_Test_Di_Aggregate_Parent */
        $result = $this->_object->create('Magento_Test_Di_Aggregate_Parent');
        $this->assertInstanceOf('Magento_Test_Di_Aggregate_Parent', $result);
        $this->assertInstanceOf('Magento_Test_Di_Child', $result->interface);
        $this->assertInstanceOf('Magento_Test_Di_Child', $result->parent);
        $this->assertInstanceOf('Magento_Test_Di_Child_A', $result->child);
        $this->assertEquals('scalarValue', $result->scalar);
        $this->assertEquals('1', $result->optionalScalar);
    }

    /**
     * @param array $arguments
     * @dataProvider createResolvesScalarCallTimeParametersAutomaticallyDataProvider
     */
    public function testCreateResolvesScalarCallTimeParametersAutomatically(array $arguments)
    {
        $this->_object->configure(array(
            'preferences' => array(
                'Magento_Test_Di_Interface' => 'Magento_Test_Di_Parent',
                'Magento_Test_Di_Parent' => 'Magento_Test_Di_Child'
            ),
        ));
        /** @var $result Magento_Test_Di_Aggregate_Child */
        $result = $this->_object->create('Magento_Test_Di_Aggregate_Child', $arguments);
        $this->assertInstanceOf('Magento_Test_Di_Aggregate_Child', $result);
        $this->assertInstanceOf('Magento_Test_Di_Child', $result->interface);
        $this->assertInstanceOf('Magento_Test_Di_Child', $result->parent);
        $this->assertInstanceOf('Magento_Test_Di_Child_A', $result->child);
        $this->assertEquals('scalarValue', $result->scalar);
        $this->assertEquals('secondScalarValue', $result->secondScalar);
        $this->assertEquals('1', $result->optionalScalar);
        $this->assertEquals('secondOptionalValue', $result->secondOptionalScalar);
    }

    public function createResolvesScalarCallTimeParametersAutomaticallyDataProvider()
    {
        return array(
            'named binding' => array(
                array(
                    'child' => 'Magento_Test_Di_Child_A',
                    'scalar' => 'scalarValue',
                    'secondScalar' => 'secondScalarValue',
                    'secondOptionalScalar' => 'secondOptionalValue'
                )
            ),
            'positional binding' => array(
                array(
                    2 => 'Magento_Test_Di_Child_A',
                    'secondOptionalScalar' => 'secondOptionalValue',
                    4 => 'secondScalarValue',
                    3 => 'scalarValue',
                )
            ),
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Ambiguous argument $child: positional and named binding is used at the same time.
     */
    public function testCreateResolveParametersAmbiguity()
    {
        $this->_object->create('Magento_Test_Di_Aggregate_WithOptional', array(
            1 => 'Magento_Test_Di_Child_A',
            'child' => 'Magento_Test_Di_Child_A',
        ));
    }

    public function testGetCreatesSharedInstancesEveryTime()
    {
        $this->_object->configure(array(
            'preferences' => array(
                'Magento_Test_Di_Interface' => 'Magento_Test_Di_Parent',
                'Magento_Test_Di_Parent' => 'Magento_Test_Di_Child'
            ),
            'Magento_Test_Di_Interface' => array(
                'shared' => 0
            ),
            'Magento_Test_Di_Aggregate_Parent' => array(
                'parameters' => array(
                    'scalar' => 'scalarValue'
                )
            )
        ));
        /** @var $result Magento_Test_Di_Aggregate_Parent */
        $result = $this->_object->create('Magento_Test_Di_Aggregate_Parent');
        $this->assertInstanceOf('Magento_Test_Di_Aggregate_Parent', $result);
        $this->assertInstanceOf('Magento_Test_Di_Child', $result->interface);
        $this->assertInstanceOf('Magento_Test_Di_Child', $result->parent);
        $this->assertInstanceOf('Magento_Test_Di_Child', $result->child);
        $this->assertNotSame($result->interface, $result->parent);
        $this->assertNotSame($result->interface, $result->child);
        $this->assertSame($result->parent, $result->child);
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Magento_Test_Di_Aggregate_Parent depends on Magento_Test_Di_Child_Circular
     */
    public function testGetDetectsCircularDependency()
    {
        $this->_object->configure(array(
            'preferences' => array(
                'Magento_Test_Di_Interface' => 'Magento_Test_Di_Parent',
                'Magento_Test_Di_Parent' => 'Magento_Test_Di_Child_Circular'
            ),
        ));
        $this->_object->create('Magento_Test_Di_Aggregate_Parent');
    }

    public function testCreateIgnoresOptionalArguments()
    {
        $instance = $this->_object->create('Magento_Test_Di_Aggregate_WithOptional');
        $this->assertNull($instance->parent);
        $this->assertNull($instance->child);
    }
}
