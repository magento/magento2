<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Controller\Adminhtml\Index;

use Magento\Ui\Controller\Adminhtml\Index\Render;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Unit test for Magento\Ui\Controller\Adminhtml\Index\Render
 */
class RenderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Render
     */
    private $render;

    /**
     * @var MockObject
     */
    private $requestMock;

    /**
     * @var MockObject
     */
    private $responseMock;

    /**
     * @var \Magento\Framework\View\Element\UiComponentFactory|MockObject
     */
    private $uiFactoryMock;

    /**
     * @var \Magento\Backend\App\Action\Context|MockObject
     */
    private $contextMock;

    /**
     * @var \Magento\Framework\AuthorizationInterface|MockObject
     */
    private $authorizationMock;

    /**
     * @var \Magento\Backend\Model\Session|MockObject
     */
    private $sessionMock;

    /**
     * @var \Magento\Framework\App\ActionFlag|MockObject
     */
    private $actionFlagMock;

    /**
     * @var \Magento\Backend\Helper\Data|MockObject
     */
    private $helperMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\Response\Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->uiFactoryMock = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponentFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->authorizationMock = $this->getMockBuilder(\Magento\Framework\AuthorizationInterface::class)
            ->getMockForAbstractClass();
        $this->sessionMock = $this->getMockBuilder(\Magento\Backend\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->actionFlagMock = $this->getMockBuilder(\Magento\Framework\App\ActionFlag::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->helperMock = $this->getMockBuilder(\Magento\Backend\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->responseMock);
        $this->contextMock->expects($this->any())
            ->method('getAuthorization')
            ->willReturn($this->authorizationMock);
        $this->contextMock->expects($this->any())
            ->method('getSession')
            ->willReturn($this->sessionMock);
        $this->contextMock->expects($this->any())
            ->method('getActionFlag')
            ->willReturn($this->actionFlagMock);
        $this->contextMock->expects($this->any())
            ->method('getHelper')
            ->willReturn($this->helperMock);

        $this->render = new Render($this->contextMock, $this->uiFactoryMock);
    }

    public function testExecuteAjaxRequest()
    {
        $name = 'test-name';
        $renderedData = '<html>data</html>';

        $this->prepareComponent($name, $renderedData);

        $this->responseMock->expects($this->never())->method('setHeader');

        $this->render->executeAjaxRequest();
    }

    public function testExecuteAjaxJsonRequest()
    {
        $name = 'test-name';
        $renderedData = '{"data": "test"}';

        $this->prepareComponent($name, $renderedData, 'json');

        $this->responseMock->expects($this->once())
            ->method('setHeader')
            ->with('Content-Type', 'application/json');

        $this->render->executeAjaxRequest();
    }

    /**
     * @param string $acl
     * @param bool $isAllowed
     * @dataProvider executeAjaxRequestWithoutPermissionsDataProvider
     */
    public function testExecuteAjaxRequestWithoutPermissions($acl, $isAllowed)
    {
        $name = 'test-name';
        $renderedData = '<html>data</html>';

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->with('namespace')
            ->willReturn($name);

        if ($isAllowed === false) {
            $this->requestMock->expects($this->once())
                ->method('isAjax')
                ->willReturn(true);
        }

        $this->responseMock->expects($this->never())
            ->method('setRedirect');
        $this->responseMock->expects($this->any())
            ->method('appendBody')
            ->with($renderedData);
        $this->authorizationMock->expects($acl ? $this->once() : $this->never())
            ->method('isAllowed')
            ->with($acl)
            ->willReturn($isAllowed);

        $componentMock = $this->getMockForAbstractClass(
            \Magento\Framework\View\Element\UiComponentInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['render']
        );

        $contextMock = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\ContextInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->any())->method('getAcceptType')->willReturn('html');

        $componentMock->expects($this->any())
            ->method('render')
            ->willReturn($renderedData);
        $componentMock->expects($this->any())
            ->method('getChildComponents')
            ->willReturn([]);
        $componentMock->expects($this->any())
            ->method('getData')
            ->with('acl')
            ->willReturn($acl);
        $componentMock->expects($this->any())
            ->method('getContext')
            ->willReturn($contextMock);
        $this->uiFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($componentMock);

        $this->render->executeAjaxRequest();
    }

    /**
     * @return array
     */
    public function executeAjaxRequestWithoutPermissionsDataProvider()
    {
        return [
            ['Magento_Test::index_index', true],
            ['Magento_Test::index_index', false],
            ['', null],
        ];
    }

    /**
     * Prepares component mock.
     *
     * @param string $namespace
     * @param string $renderedData
     * @param string $acceptType
     * @return \Magento\Framework\View\Element\UiComponentInterface|MockObject
     */
    private function prepareComponent($namespace, $renderedData, $acceptType = 'html')
    {
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->with('namespace')
            ->willReturn($namespace);
        $this->responseMock->expects($this->once())
            ->method('appendBody')
            ->with($renderedData);

        /** @var \Magento\Framework\View\Element\UiComponentInterface|MockObject $componentMock */
        $componentMock = $this->getMockForAbstractClass(
            \Magento\Framework\View\Element\UiComponentInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['render']
        );
        $contextMock = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\ContextInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->once())->method('getAcceptType')->willReturn($acceptType);

        $componentMock->expects($this->once())
            ->method('render')
            ->willReturn($renderedData);
        $componentMock->expects($this->once())
            ->method('getChildComponents')
            ->willReturn([]);
        $componentMock->expects($this->once())
            ->method('getContext')
            ->willReturn($contextMock);
        $this->uiFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($componentMock);

        return $componentMock;
    }
}
