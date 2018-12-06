<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Controller\Adminhtml\Index\Render;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\HTTP\PhpEnvironment\Response;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\UiComponent\Config\ManagerInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\LayoutInterface;
use Magento\Ui\Component\Wrapper\UiComponent;
use Magento\Ui\Controller\Adminhtml\Index\Render\Handle;

/**
 * Test for Magento\Ui\Controller\Adminhtml\Index\Render\Handle class.
 */
class HandleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $componentFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewMock;

    /**
     * @var Handle
     */
    protected $controller;

    /**
     * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $uiComponentManagerMock;

    /**
     * @var UiComponent|\PHPUnit_Framework_MockObject_MockObject
     */
    private $uiComponentMock;

    /**
     * @var AuthorizationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authorizationMock;

    /**
     * @var LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $layoutMock;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->componentFactoryMock = $this->getMockBuilder(UiComponentFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->atLeastOnce())->method('getRequest')->willReturn($this->requestMock);
        $this->responseMock = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->atLeastOnce())->method('getResponse')->willReturn($this->responseMock);
        $this->authorizationMock = $this->getMock(AuthorizationInterface::class);
        $this->viewMock = $this->getMockBuilder(ViewInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->atLeastOnce())->method('getView')->willReturn($this->viewMock);
        $this->contextMock->expects($this->atLeastOnce())
            ->method('getAuthorization')
            ->willReturn($this->authorizationMock);
        $this->uiComponentManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->uiComponentMock = $this->getMockBuilder(UiComponent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->layoutMock = $this->getMockBuilder(LayoutInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->controller = new Handle($this->contextMock, $this->componentFactoryMock, $this->uiComponentManagerMock);

    }

    /**
     * @return void
     */
    public function testExecuteNoButtons()
    {
        $isButtonExist = false;
        $isAllowed = true;
        $result = 'content';

        $this->prepareLayoutData($isButtonExist, $isAllowed);

        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('customer_listing')
            ->willReturn($this->uiComponentMock);
        $this->uiComponentMock->expects($this->once())->method('toHtml')->willReturn($result);
        $this->responseMock->expects($this->once())->method('appendBody')->with($result);

        $this->controller->execute();
    }

    /**
     * @return void
     */
    public function testExecuteWithButtons()
    {
        $isButtonExist = true;
        $isAllowed = true;
        $uiContent = 'content';
        $buttonContent = 'button';
        $templateMock = $this->getMockBuilder(Template::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->prepareLayoutData($isButtonExist, $isAllowed);

        $this->layoutMock->expects($this->at(0))
            ->method('getBlock')
            ->with('customer_listing')
            ->willReturn($this->uiComponentMock);
        $this->uiComponentMock->expects($this->once())->method('toHtml')->willReturn($uiContent);
        $this->layoutMock->expects($this->at(1))
            ->method('getBlock')
            ->with('page.actions.toolbar')
            ->willReturn($templateMock);
        $templateMock->expects($this->once())->method('toHtml')->willReturn($buttonContent);
        $this->responseMock->expects($this->once())->method('appendBody')->with($uiContent . $buttonContent);

        $this->controller->execute();
    }

    /**
     * @return void
     */
    public function testExecuteWithoutPermissions()
    {
        $isButtonExist = false;
        $isAllowed = false;

        $this->prepareLayoutData($isButtonExist, $isAllowed);

        $this->requestMock->expects($this->once())
            ->method('isAjax')
            ->willReturn(true);
        $this->layoutMock->expects($this->never())
            ->method('getBlock')
            ->willReturn($this->uiComponentMock);
        $this->uiComponentMock->expects($this->never())->method('toHtml');
        $this->responseMock->expects($this->once())->method('appendBody')->with('');

        $this->controller->execute();
    }

    /**
     * @param bool $isButtonExist
     * @param bool $isAllowed
     * @return void
     */
    private function prepareLayoutData($isButtonExist, $isAllowed)
    {
        $aclResource = 'Magento_Customer::manage';
        $handle = 'customer_index_index';
        $namespace = 'customer_listing';
        $componentConfig = [
            $namespace => [
                'arguments' => [
                    'data' => [
                        'acl' => $aclResource,
                    ],
                ],
            ],
        ];

        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('handle', null)
            ->willReturn($handle);
        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('namespace', null)
            ->willReturn($namespace);
        $this->requestMock->expects($this->at(2))
            ->method('getParam')
            ->with('buttons', false)
            ->willReturn($isButtonExist);
        $this->viewMock->expects($this->once())
            ->method('loadLayout')
            ->with(['default', $handle], true, true, false);
        $this->viewMock->expects($this->once())->method('getLayout')->willReturn($this->layoutMock);
        $this->uiComponentManagerMock->expects($this->once())
            ->method('getData')
            ->with($namespace)
            ->willReturn($componentConfig);
        $this->authorizationMock->expects($this->once())
            ->method('isAllowed')
            ->with($aclResource)
            ->willReturn($isAllowed);
    }
}
