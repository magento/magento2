<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\App;

/**
 * @magentoAppArea adminhtml
 */
class RouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\App\Router
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $this->objectManager->create('Magento\Backend\App\Router');
    }

    public function testRouterCanProcessRequestsWithProperPathInfo()
    {
        $request = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $request->expects($this->once())->method('getPathInfo')->will($this->returnValue('backend/admin/dashboard'));

        $this->assertInstanceOf('Magento\Backend\Controller\Adminhtml\Dashboard', $this->model->match($request));
    }

    /**
     * @param string $module
     * @param string $controller
     * @param string $className
     *
     * @dataProvider getControllerClassNameDataProvider
     */
    public function testGetControllerClassName($module, $controller, $className)
    {
        $this->assertEquals($className, $this->model->getActionClassName($module, $controller));
    }

    public function getControllerClassNameDataProvider()
    {
        return [
            ['Magento_Module', 'controller', 'Magento\Module\Controller\Adminhtml\Controller'],
        ];
    }

    public function testMatchCustomNoRouteAction()
    {
        if (!\Magento\TestFramework\Helper\Bootstrap::canTestHeaders()) {
            $this->markTestSkipped('Can\'t test get match without sending headers');
        }

        $routers = [
            'testmodule' => [
                'frontName' => 'testfixture',
                'id' => 'testfixture',
                'modules' => ['Magento_TestFixture'],
            ],
        ];

        $routeConfig = $this->getMock(
            'Magento\Framework\App\Route\Config',
            ['_getRoutes'],
            [
                'reader' => $this->objectManager->get('Magento\Framework\App\Route\Config\Reader'),
                'cache' => $this->objectManager->get('Magento\Framework\Config\CacheInterface'),
                'configScope' => $this->objectManager->get('Magento\Framework\Config\ScopeInterface'),
                'areaList' => $this->objectManager->get('Magento\Framework\App\AreaList'),
                'cacheId' => 'RoutesConfig'
            ]
        );

        $routeConfig->expects($this->any())->method('_getRoutes')->will($this->returnValue($routers));

        $defaultRouter = $this->objectManager->create(
            'Magento\Backend\App\Router',
            ['routeConfig' => $routeConfig]
        );

        /** @var $request \Magento\TestFramework\Request */
        $request = $this->objectManager->get('Magento\TestFramework\Request');

        $request->setPathInfo('backend/testfixture/test_controller');
        $controller = $defaultRouter->match($request);
        $this->assertInstanceOf('Magento\TestFixture\Controller\Adminhtml\Noroute', $controller);
        $this->assertEquals('noroute', $request->getActionName());
    }
}
