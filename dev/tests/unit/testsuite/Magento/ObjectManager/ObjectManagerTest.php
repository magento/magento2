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
        $config = new Magento_ObjectManager_Config_Config(new Magento_ObjectManager_Relations_Runtime());
        $factory = new Magento_ObjectManager_Interception_FactoryDecorator(
            new Magento_ObjectManager_Factory_Factory(
                $config, null, null, array('one' => 'first_val', 'two' => 'second_val')
            ), $config
        );
        $this->_object = new Magento_ObjectManager_ObjectManager($factory, $config);
    }

    public function testCreateCreatesNewInstanceEveryTime()
    {
        $objectA = $this->_object->create('Magento_Test_Di_Child');
        $this->assertInstanceOf('Magento_Test_Di_Child', $objectA);
        $objectB = $this->_object->create('Magento_Test_Di_Child');
        $this->assertInstanceOf('Magento_Test_Di_Child', $objectB);
        $this->assertNotSame($objectA, $objectB);
    }

    public function testGetCreatesNewInstanceOnlyOnce()
    {
        $objectA = $this->_object->get('Magento_Test_Di_Child');
        $this->assertInstanceOf('Magento_Test_Di_Child', $objectA);
        $objectB = $this->_object->get('Magento_Test_Di_Child');
        $this->assertInstanceOf('Magento_Test_Di_Child', $objectB);
        $this->assertSame($objectA, $objectB);
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
                    'child' => array('instance' => 'Magento_Test_Di_Child_A'),
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
                    'child' => array('instance' => 'Magento_Test_Di_Child_A'),
                    'scalar' => 'scalarValue',
                    'secondScalar' => 'secondScalarValue',
                    'secondOptionalScalar' => 'secondOptionalValue'
                )
            )
        );
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

    public function testCreateCreatesPreconfiguredInstance()
    {
        $this->_object->configure(array(
            'preferences' => array(
                'Magento_Test_Di_Interface' => 'Magento_Test_Di_Parent',
                'Magento_Test_Di_Parent' => 'Magento_Test_Di_Child'
            ),
            'customChildType' => array(
                'type' => 'Magento_Test_Di_Aggregate_Child',
                'parameters' => array(
                    'scalar' => 'configuredScalar',
                    'secondScalar' => 'configuredSecondScalar',
                    'secondOptionalScalar' => 'configuredOptionalScalar'
                )
            )
        ));
        $customChild = $this->_object->get('customChildType');
        $this->assertInstanceOf('Magento_Test_Di_Aggregate_Child', $customChild);
        $this->assertEquals('configuredScalar', $customChild->scalar);
        $this->assertEquals('configuredSecondScalar', $customChild->secondScalar);
        $this->assertEquals(1, $customChild->optionalScalar);
        $this->assertEquals('configuredOptionalScalar', $customChild->secondOptionalScalar);
        $this->assertSame($customChild, $this->_object->get('customChildType'));
    }

    public function testParameterShareabilityConfigurationIsApplied()
    {
        $this->_object->configure(array(
            'customChildType' => array(
                'type' => 'Magento_Test_Di_Aggregate_Child',
                'parameters' => array(
                    'interface' => array('instance' => 'Magento_Test_Di_Parent'),
                    'scalar' => 'configuredScalar',
                    'secondScalar' => 'configuredSecondScalar',
                )
            )
        ));
        $childA = $this->_object->create('customChildType');
        $childB = $this->_object->create('customChildType');
        $this->assertNotSame($childA, $childB);
        $this->assertSame($childA->interface, $childB->interface);

        $this->_object->configure(array(
            'customChildType' => array(
                'parameters' => array(
                    'interface' => array('instance' => 'Magento_Test_Di_Parent', 'shared' => false),
                )
            )
        ));
        $childA = $this->_object->create('customChildType');
        $childB = $this->_object->create('customChildType');
        $this->assertNotSame($childA, $childB);
        $this->assertNotSame($childA->interface, $childB->interface);
    }

    public function testTypeShareabilityConfigurationIsApplied()
    {
        $this->_object->configure(array(
            'customChildType' => array(
                'type' => 'Magento_Test_Di_Aggregate_Child',
                'parameters' => array(
                    'interface' => array('instance' => 'Magento_Test_Di_Parent'),
                    'scalar' => 'configuredScalar',
                    'secondScalar' => 'configuredSecondScalar',
                )
            )
        ));
        $childA = $this->_object->create('customChildType');
        $childB = $this->_object->create('customChildType');
        $this->assertNotSame($childA, $childB);
        $this->assertSame($childA->interface, $childB->interface);

        $this->_object->configure(array(
            'Magento_Test_Di_Parent' => array(
                'shared' => 'false'
            )
        ));
        $childA = $this->_object->create('customChildType');
        $childB = $this->_object->create('customChildType');
        $this->assertNotSame($childA, $childB);
        $this->assertNotSame($childA->interface, $childB->interface);
    }

    public function testParameterShareabilityConfigurationOverridesTypeShareability()
    {
        $this->_object->configure(array(
            'Magento_Test_Di_Parent' => array(
                'shared' => 'false'
            ),
            'customChildType' => array(
                'type' => 'Magento_Test_Di_Aggregate_Child',
                'parameters' => array(
                    'interface' => array('instance' => 'Magento_Test_Di_Parent'),
                    'scalar' => 'configuredScalar',
                    'secondScalar' => 'configuredSecondScalar',
                )
            )
        ));
        $childA = $this->_object->create('customChildType');
        $childB = $this->_object->create('customChildType');
        $this->assertNotSame($childA, $childB);
        $this->assertNotSame($childA->interface, $childB->interface);

        $this->_object->configure(array(
            'customChildType' => array(
                'parameters' => array(
                    'interface' => array('instance' => 'Magento_Test_Di_Parent', 'shared' => 'true'),
                )
            )
        ));
        $childA = $this->_object->create('customChildType');
        $childB = $this->_object->create('customChildType');
        $this->assertNotSame($childA, $childB);
        $this->assertSame($childA->interface, $childB->interface);
    }

    public function testGlobalArgumentsCanBeConfigured()
    {
        $this->_object->configure(array(
            'preferences' => array(
                'Magento_Test_Di_Interface' => 'Magento_Test_Di_Parent',
            ),
            'Magento_Test_Di_Aggregate_Parent' => array(
                'parameters' => array(
                    'scalar' => array('argument' => 'Magento_Test_Di_Aggregate_Interface::PARAM_ONE'),
                    'optionalScalar' => array('argument' => 'Magento_Test_Di_Aggregate_Interface::PARAM_TWO')
                )
            )
        ));
        /** @var $result Magento_Test_Di_Aggregate_Parent */
        $result = $this->_object->create('Magento_Test_Di_Aggregate_Parent');
        $this->assertEquals('first_val', $result->scalar);
        $this->assertEquals('second_val', $result->optionalScalar);
    }
}
