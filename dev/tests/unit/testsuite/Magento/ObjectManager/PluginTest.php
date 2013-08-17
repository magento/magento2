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
require_once __DIR__ . '/../_files/Child.php';
require_once __DIR__ . '/../_files/Child/A.php';
require_once __DIR__ . '/../_files/Child/Circular.php';
require_once __DIR__ . '/../_files/Child/Interceptor/A.php';
require_once __DIR__ . '/../_files/Child/Interceptor/B.php';
require_once __DIR__ . '/../_files/Aggregate/Interface.php';
require_once __DIR__ . '/../_files/Aggregate/Parent.php';
require_once __DIR__ . '/../_files/Aggregate/Child.php';
require_once __DIR__ . '/../_files/Aggregate/WithOptional.php';
require_once __DIR__ . '/../_files/Child/Interceptor.php';
require_once __DIR__ . '/../_files/Child/Interceptor/A.php';
require_once __DIR__ . '/../_files/Child/Interceptor/B.php';

class Magento_ObjectManager_PluginTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_ObjectManager
     */
    protected $_locator;

    protected function setUp()
    {
        $config = new Magento_ObjectManager_Config_Config();
        $factory = new Magento_ObjectManager_Interception_FactoryDecorator(
            new Magento_ObjectManager_Factory_Factory($config), $config
        );
        $this->_locator = new Magento_ObjectManager_ObjectManager($factory, $config);
    }

    public function testPluginsAreCalled()
    {
        $this->_locator->configure(
            array('Magento_Test_Di_Child' => array(
                'plugins' => array(
                    'first' => array('instance' => 'Magento_Test_Di_Child_Interceptor_A'),
                    'second' => array('instance' => 'Magento_Test_Di_Child_Interceptor_B')
                )
            ))
        );

        $child = $this->_locator->create('Magento_Test_Di_Child');
        $this->assertEquals('_A__B_|BAtestStringAB|_B__A_', $child->wrap('testString'));
    }

    public function testPluginsAreOrdered()
    {
        $this->_locator->configure(
            array('Magento_Test_Di_Child' => array(
                'plugins' => array(
                    'first' => array('instance' => 'Magento_Test_Di_Child_Interceptor_A'),
                    'second' => array('instance' => 'Magento_Test_Di_Child_Interceptor_B', 'sortOrder' => '0')
                )
            ))
        );

        $child = $this->_locator->create('Magento_Test_Di_Child');
        $this->assertEquals('_B__A_|ABtestStringBA|_A__B_', $child->wrap('testString'));
    }

    public function testPluginsAreAddedToInstances()
    {
        $this->_locator->configure(
            array('customChild' => array(
                'type' => 'Magento_Test_Di_Child',
                'plugins' => array(
                    'first' => array('instance' => 'Magento_Test_Di_Child_Interceptor_A'),
                    'second' => array('instance' => 'Magento_Test_Di_Child_Interceptor_B')
                ),
                'parameters' => array(
                    'wrapperSymbol' => '/'
                )
            ))
        );

        $child = $this->_locator->create('customChild');
        $this->assertEquals('_A__B_/BAtestStringAB/_B__A_', $child->wrap('testString'));
    }

    public function testInstanceIsUsedAsPlugin()
    {
        $this->_locator->configure(
            array(
                'Magento_Test_Di_Child' => array(
                    'plugins' => array(
                        'first' => array('instance' => 'customAInterceptor'),
                        'second' => array('instance' => 'Magento_Test_Di_Child_Interceptor_B')
                    )
                ),
                'customAInterceptor' => array(
                    'type' => 'Magento_Test_Di_Child_Interceptor_A',
                    'parameters' => array(
                        'wrapperSym' => 'AAA'
                    )
                )
            )
        );

        $child = $this->_locator->create('Magento_Test_Di_Child');
        $this->assertEquals('_AAA__B_|BAAAtestStringAAAB|_B__AAA_', $child->wrap('testString'));
    }

    public function testCreateReturnsNewInterceptorEveryTime()
    {
        $this->_locator->configure(array(
            'Magento_Test_Di_Child' => array(
                'plugins' => array(
                    'first' => array('instance' => 'Magento_Test_Di_Child_Interceptor_A'),
                    'second' => array('instance' => 'Magento_Test_Di_Child_Interceptor_B')
                )
            )
        ));

        $childOne = $this->_locator->create('Magento_Test_Di_Child');
        $childTwo = $this->_locator->create('Magento_Test_Di_Child');
        $this->assertEquals($childOne->wrap('testString'), $childTwo->wrap('testString'));
        $this->assertNotSame($childOne, $childTwo);
    }

    public function testGetReturnsSameInterceptorEveryTime()
    {
        $this->_locator->configure(array(
            'Magento_Test_Di_Child' => array(
                'plugins' => array(
                    'first' => array('instance' => 'Magento_Test_Di_Child_Interceptor_A'),
                    'second' => array('instance' => 'Magento_Test_Di_Child_Interceptor_B')
                )
            )
        ));

        $childOne = $this->_locator->get('Magento_Test_Di_Child');
        $childTwo = $this->_locator->get('Magento_Test_Di_Child');
        $this->assertEquals($childOne->wrap('testString'), $childTwo->wrap('testString'));
        $this->assertSame($childOne, $childTwo);
    }

    public function testInterfacePluginsAreAppliedToImplementation()
    {
        $this->_locator->configure(array(
            'preferences' => array(
                'Magento_Test_Di_Interface' => 'Magento_Test_Di_Child'
            ),
            'Magento_Test_Di_Interface' => array(
                'plugins' => array(
                    'first' => array('instance' => 'Magento_Test_Di_Child_Interceptor_A'),
                )
            ),
            'Magento_Test_Di_Parent' => array(
                'plugins' => array(
                    'second' => array('instance' => 'Magento_Test_Di_Child_Interceptor_B')
                )
            )
        ));

        $childOne = $this->_locator->create('Magento_Test_Di_Interface');
        $childTwo = $this->_locator->create('Magento_Test_Di_Child');
        $this->assertEquals($childOne, $childTwo);
        $this->assertInstanceOf('Magento_Test_Di_Child', $childOne);
        $this->assertEquals('_A__B_|BAtestStringAB|_B__A_', $childOne->wrap('testString'));
        $this->assertEquals('_A__B_|BAtestStringAB|_B__A_', $childTwo->wrap('testString'));
    }
}
