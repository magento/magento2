<?php
/**
 * Tests Magento\Core\App\Router\NoRouteHandler
 *
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
namespace Magento\Core\App\Router;

class NoRouteHandlerTest extends \Magento\Test\BaseTestCase
{
    /**
     * @var \Magento\Core\App\Router\NoRouteHandler
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $configMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\RequestInterface
     */
    private $requestMock;

    public function setUp()
    {
        parent::setUp();
        $this->configMock = $this->basicMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $requestMethods = [
            'getActionName',
            'getModuleName',
            'getParam',
            'setActionName',
            'setModuleName',
            'setControllerName',
            'getCookie',
        ];
        $this->requestMock = $this->getMock(
            'Magento\Framework\App\RequestInterface',
            $requestMethods
        );
        $this->model = $this->objectManager->getObject('Magento\Core\App\Router\NoRouteHandler',
            [
                'config' => $this->configMock,
            ]
        );
    }

    public function testProcessDefault()
    {
        // Default path from config
        $default = 'moduleName/actionPath/actionName';
        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with('web/default/no_route', 'default')
            ->willReturn($default);

        // Set expectations
        $this->requestMock->expects($this->once())
            ->method('setModuleName')
            ->with('moduleName')
            ->willReturnSelf();
        $this->requestMock->expects($this->once())
            ->method('setControllerName')
            ->with('actionPath')
            ->willReturnSelf();
        $this->requestMock->expects($this->once())
            ->method('setActionName')
            ->with('actionName')
            ->willReturnSelf();

        // Test
        $this->assertTrue($this->model->process($this->requestMock));
    }

    public function testProcessNoDefault()
    {
        // Default path from config
        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with('web/default/no_route', 'default')
            ->willReturn(null);

        // Set expectations
        $this->requestMock->expects($this->once())
            ->method('setModuleName')
            ->with('core')
            ->willReturnSelf();
        $this->requestMock->expects($this->once())
            ->method('setControllerName')
            ->with('index')
            ->willReturnSelf();
        $this->requestMock->expects($this->once())
            ->method('setActionName')
            ->with('index')
            ->willReturnSelf();

        // Test
        $this->assertTrue($this->model->process($this->requestMock));
    }
} 