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
 * @package     Magento_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Core\Controller\Varien;

class ActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Controller\Varien\Action|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_object;

    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_objectManager->get('Magento\View\DesignInterface')
            ->setArea(\Magento\Core\Model\App\Area::AREA_FRONTEND)
            ->setDefaultDesignTheme();
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var $request \Magento\TestFramework\Request */
        $request = $objectManager->get('Magento\App\RequestInterface');
        $arguments = array(
            'request'  => $request,
            'response' => $this->_objectManager->get('Magento\TestFramework\Response'),
        );
        $this->_objectManager->get('Magento\View\DesignInterface')
            ->setArea(\Magento\Core\Model\App\Area::AREA_FRONTEND)
            ->setDefaultDesignTheme();
        $context = $this->_objectManager->create('Magento\Core\Controller\Varien\Action\Context', $arguments);
        $this->_object = $this->getMockForAbstractClass(
            'Magento\Core\Controller\Varien\Action',
            array('context' => $context)
        );
    }

    public function testHasAction()
    {
        $this->assertFalse($this->_object->hasAction('test'));
        $this->assertTrue($this->_object->hasAction('noroute'));
    }

    public function testGetRequest()
    {
        $this->assertInstanceOf('Magento\TestFramework\Request', $this->_object->getRequest());
    }

    public function testGetResponse()
    {
        $this->assertInstanceOf('Magento\TestFramework\Response', $this->_object->getResponse());
    }

    public function testSetGetFlag()
    {
        $this->assertEmpty($this->_object->getFlag(''));

        $this->_object->setFlag('test', 'test_flag', 'test_value');
        $this->assertFalse($this->_object->getFlag('', 'test_flag'));
        $this->assertEquals('test_value', $this->_object->getFlag('test', 'test_flag'));
        $this->assertNotEmpty($this->_object->getFlag(''));

        $this->_object->setFlag('', 'test', 'value');
        $this->assertEquals('value', $this->_object->getFlag('', 'test'));
    }

    public function testGetFullActionName()
    {
        /* empty request */
        $this->assertEquals('__', $this->_object->getFullActionName());

        $this->_object->getRequest()->setRouteName('test')
            ->setControllerName('controller')
            ->setActionName('action');
        $this->assertEquals('test/controller/action', $this->_object->getFullActionName('/'));
    }

    /**
     * @param string $controllerClass
     * @param string $expectedArea
     * @dataProvider controllerAreaDesignDataProvider
     * @magentoAppIsolation enabled
     */
    public function testGetLayout($controllerClass, $expectedArea)
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Model\App')
            ->loadArea($expectedArea);

        $this->_objectManager->get('Magento\Config\ScopeInterface')->setCurrentScope($expectedArea);
        /** @var $controller \Magento\Core\Controller\Varien\Action */
        $controller = $this->_objectManager->create($controllerClass);
        $this->assertInstanceOf('Magento\Core\Model\Layout', $controller->getLayout());
        $this->assertEquals($expectedArea, $controller->getLayout()->getArea());
    }

    /**
     * @magentoAppIsolation enabled
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Layout must be loaded only once.
     */
    public function testLoadLayoutThrowsExceptionWhenCalledTwice()
    {
        $this->_object->loadLayout();
        $this->assertContains('default', $this->_object->getLayout()->getUpdate()->getHandles());
        $this->assertInstanceOf('Magento\Core\Block\AbstractBlock', $this->_object->getLayout()->getBlock('root'));
        $this->_object->loadLayout('test');
    }

    public function testGetDefaultLayoutHandle()
    {
        $this->_object->getRequest()
            ->setRouteName('Test')
            ->setControllerName('Controller')
            ->setActionName('Action');
        $this->assertEquals('test_controller_action', $this->_object->getDefaultLayoutHandle());
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
        $this->_object->getRequest()
            ->setRouteName($route)
            ->setControllerName($controller)
            ->setActionName($action);
        $this->_object->addActionLayoutHandles();
        $handles = $this->_object->getLayout()->getUpdate()->getHandles();

        foreach ($expected as $expectedHandle) {
            $this->assertContains($expectedHandle, $handles);
        }
        foreach ($nonExpected as $nonExpectedHandle) {
            $this->assertNotContains($nonExpectedHandle, $handles);
        }
    }

    /**
     * @return array
     */
    public function addActionLayoutHandlesDataProvider()
    {
        return array(
            array('Test', 'Controller', 'Action', array('test_controller_action'),
                array(
                    'STORE_' . \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
                        ->get('Magento\Core\Model\StoreManagerInterface')->getStore()->getCode()
                )
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
     * @dataProvider addActionLayoutHandlesInheritedDataProvider
     */
    public function testAddActionLayoutHandlesInherited($route, $controller, $action, $expected)
    {
        $arguments = array(
            'request'  => $this->_objectManager->get('Magento\TestFramework\Request'),
            'response' => $this->_objectManager->get('Magento\TestFramework\Response'),
            'isRenderInherited' => true,
        );
        $context = $this->_objectManager->create('Magento\Core\Controller\Varien\Action\Context', $arguments);
        $this->_object = $this->getMockForAbstractClass('Magento\Core\Controller\Varien\Action',
            array('context' => $context));

        $this->_object->getRequest()
            ->setRouteName($route)
            ->setControllerName($controller)
            ->setActionName($action);
        $this->_object->addActionLayoutHandles();
        $handles = $this->_object->getLayout()->getUpdate()->getHandles();
        foreach ($expected as $expectedHandle) {
            $this->assertContains($expectedHandle, $handles);
        }
    }

    /**
     * @return array
     */
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
        $this->_object->getRequest()->setRouteName('test')
            ->setControllerName('controller')
            ->setActionName('action');
        $result = $this->_object->addPageLayoutHandles();
        $this->assertFalse($result);
        $this->assertEmpty($this->_object->getLayout()->getUpdate()->getHandles());

        $this->_object->getRequest()->setRouteName('catalog')
            ->setControllerName('product')
            ->setActionName('view');
        $result = $this->_object->addPageLayoutHandles(array('type' => 'simple'));
        $this->assertTrue($result);
        $handles = $this->_object->getLayout()->getUpdate()->getHandles();
        $this->assertContains('default', $handles);
        $this->assertContains('catalog_product_view', $handles);
        $this->assertContains('catalog_product_view_type_simple', $handles);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     */
    public function testRenderLayout()
    {
        $this->_object->loadLayout();
        $this->assertEmpty($this->_object->getResponse()->getBody());
        $this->_object->renderLayout();
        $this->assertNotEmpty($this->_object->getResponse()->getBody());
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testDispatch()
    {
        if (headers_sent()) {
            $this->markTestSkipped('Can\' dispatch - headers already sent');
        }
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var $request \Magento\TestFramework\Request */
        $request = $objectManager->get('Magento\TestFramework\Request');
        $request->setDispatched();

        $arguments = array(
            'request'  => $request,
            'response' => $this->_objectManager->get('Magento\TestFramework\Response'),
        );
        $context = $this->_objectManager->create('Magento\Core\Controller\Varien\Action\Context', $arguments);

        /* Area-specific controller is used because area must be known at the moment of loading the design */
        $this->_object = $this->_objectManager->create(
            'Magento\Core\Controller\Front\Action',
            array('context'  => $context)
        );
        $this->_objectManager->get('Magento\Config\ScopeInterface')->setCurrentScope('frontend');
        $this->_object->dispatch('not_exists');

        $this->assertFalse($request->isDispatched());
        $this->assertEquals('cms', $request->getModuleName());
        $this->assertEquals('index', $request->getControllerName());
        $this->assertEquals('noRoute', $request->getActionName());
    }

    public function testGetActionMethodName()
    {
        $this->assertEquals('testAction', $this->_object->getActionMethodName('test'));
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     */
    public function testNoCookiesAction()
    {
        $this->assertEmpty($this->_object->getResponse()->getBody());
        $this->_object->noCookiesAction();
        $redirect = array(
            'name' => 'Location',
            'value' => 'http://localhost/index.php/enable-cookies',
            'replace' => true,
        );
        $this->assertEquals($redirect, $this->_object->getResponse()->getHeader('Location'));
    }

    /**
     * @magentoAppIsolation enabled
     * @dataProvider controllerAreaDesignDataProvider
     *
     * @param string $controllerClass
     * @param string $expectedArea
     * @param string $expectedStore
     * @param string $expectedDesign
     * @param string $context
     */
    public function testPreDispatch($controllerClass, $expectedArea, $expectedStore, $expectedDesign, $context)
    {
        $themes = array('frontend' => 'magento_blank', 'adminhtml' => 'magento_backend', 'install' => 'magento_basic');
        $design = $this->_objectManager->create('Magento\Core\Model\View\Design', array('themes' => $themes));
        $this->_objectManager->addSharedInstance($design, 'Magento\Core\Model\View\Design');

        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Model\App')
            ->loadArea($expectedArea);
        /** @var $controller \Magento\Core\Controller\Varien\Action */
        $context = $this->_objectManager->create($context, array(
            'response' => $this->_objectManager->get('Magento\TestFramework\Response')
        ));
        $controller = $this->_objectManager->create($controllerClass, array('context' => $context));
        $controller->preDispatch();

        $design = $this->_objectManager->get('Magento\View\DesignInterface');

        $this->assertEquals($expectedArea, $design->getArea());
        $this->assertEquals($expectedStore, \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Core\Model\StoreManagerInterface')->getStore()->getCode());
        if ($expectedDesign) {
            $this->assertEquals($expectedDesign, $design->getDesignTheme()->getThemePath());
        }
    }

    /**
     * @return array
     */
    public function controllerAreaDesignDataProvider()
    {
        return array(
            'install' => array(
                'Magento\Install\Controller\Action',
                'install',
                'default',
                'magento_basic',
                'Magento\Core\Controller\Varien\Action\Context'
            ),
            'frontend' => array(
                'Magento\Core\Controller\Front\Action',
                'frontend',
                'default',
                'magento_blank',
                'Magento\Core\Controller\Varien\Action\Context'
            ),
            'backend' => array(
                'Magento\Adminhtml\Controller\Action',
                'adminhtml',
                'admin',
                'magento_backend',
                'Magento\Backend\Controller\Context'
            ),
        );
    }

    /**
     * @magentoAppArea frontend
     */
    public function testNoRouteAction()
    {
        $status = 'test';
        $this->_object->getRequest()->setParam('__status__', $status);
        $caughtException = false;
        $message = '';
        try {
            $this->_object->norouteAction();
        } catch (\Exception $e) {
            $caughtException = true;
            $message = $e->getMessage();
        }
        $this->assertFalse($caughtException, $message);
    }
}
