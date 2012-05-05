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
 * @category    Magento
 * @package     Mage_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Controller_Varien_Router_BaseTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Controller_Varien_Router_Base
     */
    protected $_model;

    protected function setUp()
    {
        $options = array(
            'area' => 'frontend',
            'base_controller' => 'Mage_Core_Controller_Front_Action'
        );
        $this->_model = new Mage_Core_Controller_Varien_Router_Base($options);
        $this->_model->setFront(Mage::app()->getFrontController());
    }

    /**
     * @dataProvider initOptionsDataProvider
     * @expectedException Mage_Core_Exception
     */
    public function testConstructor(array $options)
    {
        new Mage_Core_Controller_Varien_Router_Base($options);
    }

    public function initOptionsDataProvider()
    {
        return array(
            array(
                array()
            ),
            array(
                array('area' => 'frontend')
            ),
            array(
                array('base_controller' => 'Mage_Core_Controller_Front_Action')
            )
        );
    }

    public function testCollectRoutes()
    {
        $this->_model->collectRoutes('frontend', 'standard');
        $this->assertEquals('catalog', $this->_model->getFrontNameByRoute('catalog'));
    }

    public function testFetchDefault()
    {
        $default = array(
            'module' => 'core',
            'controller' => 'index',
            'action' => 'index'
        );
        $this->_model->fetchDefault();
        $this->assertEquals($default, Mage::app()->getFrontController()->getDefault());
    }

    public function testMatch()
    {
        if (!Magento_Test_Bootstrap::canTestHeaders()) {
            $this->markTestSkipped('Can\'t test get match without sending headers');
        }

        $request = new Magento_Test_Request();
        $this->assertFalse($this->_model->match($request));

        $this->_model->collectRoutes('frontend', 'standard');
        $this->assertTrue($this->_model->match($request));
        $request->setRequestUri('core/index/index');
        $this->assertTrue($this->_model->match($request));

        $request->setPathInfo('not_exists/not_exists/not_exists')
            ->setModuleName('not_exists')
            ->setControllerName('not_exists')
            ->setActionName('not_exists');
        $this->assertFalse($this->_model->match($request));
    }

    /**
     * @covers Mage_Core_Controller_Varien_Router_Base::addModule
     * @covers Mage_Core_Controller_Varien_Router_Base::getModuleByFrontName
     * @covers Mage_Core_Controller_Varien_Router_Base::getRouteByFrontName
     * @covers Mage_Core_Controller_Varien_Router_Base::getFrontNameByRoute
     */
    public function testAddModuleAndGetters()
    {
        $this->_model->addModule('test_front', 'test_name', 'test_route');
        $this->assertEquals('test_name', $this->_model->getModuleByFrontName('test_front'));
        $this->assertEquals('test_route', $this->_model->getRouteByFrontName('test_front'));
        $this->assertEquals('test_front', $this->_model->getFrontNameByRoute('test_route'));
    }

    public function testGetModuleByName()
    {
        $this->assertTrue($this->_model->getModuleByName('test', array('test')));
    }

    /**
     * @covers Mage_Core_Controller_Varien_Router_Base::getControllerFileName
     * @covers Mage_Core_Controller_Varien_Router_Base::validateControllerFileName
     */
    public function testGetControllerFileName()
    {
        $file = $this->_model->getControllerFileName('Mage_Core', 'index');
        $this->assertStringEndsWith('IndexController.php', $file);
        $this->assertTrue($this->_model->validateControllerFileName($file));
        $this->assertFalse($this->_model->validateControllerFileName(''));
    }

    public function testGetControllerClassName()
    {
        $this->assertEquals('Mage_Core_IndexController', $this->_model->getControllerClassName('Mage_Core', 'index'));
    }
}
