<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Controller\Adminhtml\Index;

use Magento\Ui\Controller\Adminhtml\Index\Render;
use Magento\Ui\Model\UiComponentTypeResolver;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class RenderTest
 */
class RenderTest extends \PHPUnit\Framework\TestCase
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
     * @var \PHPUnit_Framework_MockObject_MockObject|UiComponentTypeResolver
     */
    private $uiComponentTypeResolverMock;

    protected function setUp()
    {
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\Response\Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $contextMock->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->responseMock);

        $this->uiFactoryMock = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponentFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->uiComponentTypeResolverMock = $this->getMockBuilder(UiComponentTypeResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->render = new Render($contextMock, $this->uiFactoryMock, $this->uiComponentTypeResolverMock);
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
            \Magento\Framework\View\Element\UiComponentInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['render']
        );
        $contextMock = $this->createMock(ContextInterface::class);

        $viewMock->expects($this->once())
            ->method('render')
            ->willReturn($renderedData);
        $viewMock->expects($this->once())
            ->method('getChildComponents')
            ->willReturn([]);
        $viewMock->expects($this->atLeastOnce())->method('getContext')->willReturn($contextMock);
        $this->uiFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($viewMock);
        $this->uiComponentTypeResolverMock->expects($this->once())->method('resolve')->with($contextMock)
            ->willReturn('application/json');
        $this->responseMock->expects($this->once())->method('setHeader')
            ->with('Content-Type', 'application/json', true);

        $this->render->executeAjaxRequest();
    }
}
