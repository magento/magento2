<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Controller\Adminhtml\Index;

use \Magento\Ui\Controller\Adminhtml\Index\Render;

/**
 * Class RenderTest
 */
class RenderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Render
     */
    protected $render;

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
    protected $uiFactoryMock;

    /**
     * @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var \Magento\Framework\AuthorizationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authorizationMock;

    /**
     * @var \Magento\Backend\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sessionMock;

    /**
     * @var \Magento\Framework\App\ActionFlag|\PHPUnit_Framework_MockObject_MockObject
     */
    private $actionFlagMock;

    /**
     * @var \Magento\Backend\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    private $helperMock;

    protected function setUp()
    {
        $this->requestMock = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this->getMockBuilder('Magento\Framework\App\Response\Http')
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock = $this->getMockBuilder('Magento\Backend\App\Action\Context')
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

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->with('namespace')
            ->willReturn($name);
        $this->requestMock->expects($this->any())
            ->method('getParams')
            ->willReturn([]);
        $this->responseMock->expects($this->once())
            ->method('appendBody')
            ->with($renderedData);

        /**
         * @var \Magento\Framework\View\Element\UiComponentInterface|\PHPUnit_Framework_MockObject_MockObject $viewMock
         */
        $viewMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\Element\UiComponentInterface',
            [],
            '',
            false,
            true,
            true,
            ['render']
        );
        $viewMock->expects($this->once())
            ->method('render')
            ->willReturn($renderedData);
        $viewMock->expects($this->once())
            ->method('getChildComponents')
            ->willReturn([]);
        $this->uiFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($viewMock);

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
        $this->requestMock->expects($this->any())
            ->method('getParams')
            ->willReturn([]);
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
}
