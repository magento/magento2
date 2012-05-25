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

class Mage_Core_Controller_Varien_ActionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Controller_Varien_Action|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = $this->getMockForAbstractClass(
            'Mage_Core_Controller_Varien_Action',
            array(new Magento_Test_Request(), new Magento_Test_Response())
        );
    }

    public function testHasAction()
    {
        $this->assertFalse($this->_model->hasAction('test'));
        $this->assertTrue($this->_model->hasAction('noroute'));
    }

    public function testGetRequest()
    {
        $this->assertInstanceOf('Magento_Test_Request', $this->_model->getRequest());
    }

    public function testGetResponse()
    {
        $this->assertInstanceOf('Magento_Test_Response', $this->_model->getResponse());
    }

    public function testSetGetFlag()
    {
        $this->assertEmpty($this->_model->getFlag(''));

        $this->_model->setFlag('test', 'test_flag', 'test_value');
        $this->assertFalse($this->_model->getFlag('', 'test_flag'));
        $this->assertEquals('test_value', $this->_model->getFlag('test', 'test_flag'));
        $this->assertNotEmpty($this->_model->getFlag(''));

        $this->_model->setFlag('', 'test', 'value');
        $this->assertEquals('value', $this->_model->getFlag('', 'test'));
    }

    public function testGetFullActionName()
    {
        /* empty request */
        $this->assertEquals('__', $this->_model->getFullActionName());

        $this->_model->getRequest()->setRouteName('test')
            ->setControllerName('controller')
            ->setActionName('action');
        $this->assertEquals('test/controller/action', $this->_model->getFullActionName('/'));
    }

    /**
     * @param string $controllerClass
     * @param string $expectedArea
     * @dataProvider controllerAreaDesignDataProvider
     * @magentoAppIsolation enabled
     */
    public function testGetLayout($controllerClass, $expectedArea)
    {
        /** @var $controller Mage_Core_Controller_Varien_Action */
        $controller = new $controllerClass(new Magento_Test_Request(), new Magento_Test_Response());
        $this->assertInstanceOf('Mage_Core_Model_Layout', $controller->getLayout());
        $this->assertEquals($expectedArea, $controller->getLayout()->getArea());
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testLoadLayout()
    {
        $this->_model->loadLayout();
        $this->assertContains('default', $this->_model->getLayout()->getUpdate()->getHandles());

        $this->_model->loadLayout('test');
        $this->assertContains('test', $this->_model->getLayout()->getUpdate()->getHandles());

        $this->assertInstanceOf('Mage_Core_Block_Abstract', $this->_model->getLayout()->getBlock('root'));
    }

    public function testGetDefaultLayoutHandle()
    {
        $this->_model->getRequest()
            ->setRouteName('Test')
            ->setControllerName('Controller')
            ->setActionName('Action');
        $this->assertEquals('test_controller_action', $this->_model->getDefaultLayoutHandle());
    }

    /**
     * @param string $route
     * @param string $controller
     * @param string $action
     * @param array $expected
     * @param array $nonExpected
     *
     * @magentoAppIsolation enabled
     * @dataProvider addActionLayoutHandlesDataProvider
     */
    public function testAddActionLayoutHandles($route, $controller, $action, $expected, $nonExpected)
    {
        $this->_model->getRequest()
            ->setRouteName($route)
            ->setControllerName($controller)
            ->setActionName($action);
        $this->_model->addActionLayoutHandles();
        $handles = $this->_model->getLayout()->getUpdate()->getHandles();

        foreach ($expected as $expectedHandle) {
            $this->assertContains($expectedHandle, $handles);
        }
        foreach ($nonExpected as $nonExpectedHandle) {
            $this->assertNotContains($nonExpectedHandle, $handles);
        }
    }

    public function addActionLayoutHandlesDataProvider()
    {
        return array(
            array('Test', 'Controller', 'Action', array('test_controller_action'),
                array('STORE_' . Mage::app()->getStore()->getCode())
            ),
            array('catalog', 'product', 'gallery', array('catalog_product_gallery'),
                array('default', 'catalog_product_view')
            )
        );
    }

    /**
     * @param string $route
     * @param string $controller
     * @param string $action
     * @param array $expected
     *
     * @magentoAppIsolation enabled
     * @magentoConfigFixture global/dev/page_type/render_inherited 1
     * @dataProvider addActionLayoutHandlesInheritedDataProvider
     */
    public function testAddActionLayoutHandlesInherited($route, $controller, $action, $expected)
    {
        $this->_model->getRequest()
            ->setRouteName($route)
            ->setControllerName($controller)
            ->setActionName($action);
        $this->_model->addActionLayoutHandles();
        $handles = $this->_model->getLayout()->getUpdate()->getHandles();
        foreach ($expected as $expectedHandle) {
            $this->assertContains($expectedHandle, $handles);
        }
    }

    public function addActionLayoutHandlesInheritedDataProvider()
    {
        return array(
            array('test', 'controller', 'action', array('test_controller_action')),
            array('catalog', 'product', 'gallery', array('default', 'catalog_product_view', 'catalog_product_gallery'))
        );
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testAddPageLayoutHandles()
    {
        $this->_model->getRequest()->setRouteName('test')
            ->setControllerName('controller')
            ->setActionName('action');
        $result = $this->_model->addPageLayoutHandles();
        $this->assertFalse($result);
        $this->assertEmpty($this->_model->getLayout()->getUpdate()->getHandles());

        $this->_model->getRequest()->setRouteName('catalog')
            ->setControllerName('product')
            ->setActionName('view');
        $result = $this->_model->addPageLayoutHandles(array('type' => 'simple'));
        $this->assertTrue($result);
        $handles = $this->_model->getLayout()->getUpdate()->getHandles();
        $this->assertContains('default', $handles);
        $this->assertContains('catalog_product_view', $handles);
        $this->assertContains('catalog_product_view_type_simple', $handles);
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testRenderLayout()
    {
        $this->_model->loadLayout();
        $this->assertEmpty($this->_model->getResponse()->getBody());
        $this->_model->renderLayout();
        $this->assertNotEmpty($this->_model->getResponse()->getBody());
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testDispatch()
    {
        if (headers_sent()) {
            $this->markTestSkipped('Can\' dispatch - headers already sent');
        }
        $request = new Magento_Test_Request();
        $request->setDispatched();

        /* Area-specific controller is used because area must be known at the moment of loading the design */
        $this->_model = new Mage_Core_Controller_Front_Action($request, new Magento_Test_Response());
        $this->_model->dispatch('not_exists');

        $this->assertFalse($request->isDispatched());
        $this->assertEquals('cms', $request->getModuleName());
        $this->assertEquals('index', $request->getControllerName());
        $this->assertEquals('noRoute', $request->getActionName());
    }

    public function testGetActionMethodName()
    {
        $this->assertEquals('testAction', $this->_model->getActionMethodName('test'));
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testNoCookiesAction()
    {
        $this->assertEmpty($this->_model->getResponse()->getBody());
        $this->_model->noCookiesAction();
        $this->assertNotEmpty($this->_model->getResponse()->getBody());
    }

    /**
     * @magentoConfigFixture               install/design/theme/full_name   default/default/default
     * @magentoConfigFixture               adminhtml/design/theme/full_name default/default/default
     * @magentoConfigFixture current_store design/theme/full_name           default/iphone/default
     * @magentoAppIsolation  enabled
     *
     * @dataProvider controllerAreaDesignDataProvider
     *
     * @param string $controllerClass
     * @param string $expectedArea
     * @param string $expectedStore
     * @param string $expectedDesign
     */
    public function testPreDispatch($controllerClass, $expectedArea, $expectedStore, $expectedDesign)
    {
        /** @var $controller Mage_Core_Controller_Varien_Action */
        $controller = new $controllerClass(new Magento_Test_Request(), new Magento_Test_Response());
        $controller->preDispatch();
        $this->assertEquals($expectedArea, Mage::getDesign()->getArea());
        $this->assertEquals($expectedStore, Mage::app()->getStore()->getCode());
        if ($expectedDesign) {
            $this->assertEquals($expectedDesign, Mage::getDesign()->getDesignTheme());
        }
    }

    public function controllerAreaDesignDataProvider()
    {
        return array(
            'install'  => array('Mage_Install_Controller_Action',    'install',   'default', 'default/default/default'),
            'frontend' => array('Mage_Core_Controller_Front_Action', 'frontend',  'default', 'default/iphone/default'),
            'backend'  => array('Mage_Adminhtml_Controller_Action',  'adminhtml', 'admin',   'default/default/default'),
            'api'      => array('Mage_Api_Controller_Action',        'adminhtml', 'admin',   ''),
        );
    }

    public function testNoRouteAction()
    {
        $status = 'test';
        $this->_model->getRequest()->setParam('__status__', $status);
        $caughtException = false;
        $message = '';
        try {
            $this->_model->norouteAction();
        } catch (Exception $e) {
            $caughtException = true;
            $message = $e->getMessage();
        }
        $this->assertFalse($caughtException, $message);
    }

    public function controllerAreaSetDataProvider()
    {
        return array(
            'frontend' => array('Mage_Core_Controller_Front_Action', 'frontend', 'frontend'),
            'adminhtml' => array('Mage_Core_Controller_Front_Action', 'adminhtml', 'adminhtml'),
            'test' => array('Mage_Core_Controller_Front_Action', 'test', 'test'),

        );
    }

    /**
     * @param string $controllerClass
     * @param string $setArea
     * @param string $expectedArea
     * @dataProvider controllerAreaSetDataProvider
     * @magentoAppIsolation enabled
     */
    public function testSetCurrentArea($controllerClass, $setArea, $expectedArea)
    {
        /** @var $controller Mage_Core_Controller_Varien_Action */
        $controller = new $controllerClass(new Magento_Test_Request(), new Magento_Test_Response());
        $this->assertInstanceOf($controllerClass, $controller->setCurrentArea($setArea));
        $this->assertEquals($expectedArea, $controller->getLayout()->getArea());
    }

}
