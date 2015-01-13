<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Controller\Adminhtml\Index;

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

    public function setUp()
    {
        $this->requestMock = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this->getMockBuilder('Magento\Framework\App\Response\Http')
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock = $this->getMockBuilder('Magento\Backend\App\Action\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $contextMock->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->responseMock);

        $this->uiFactoryMock = $this->getMockBuilder('Magento\Framework\View\Element\UiComponentFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->render = new Render($contextMock, $this->uiFactoryMock);
    }

    public function testExecute()
    {
        $name = 'test-name';
        $renderedData = '<html>data</html>';

        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('component')
            ->willReturn($name);
        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('name')
            ->willReturn($name);
        $this->responseMock->expects($this->once())
            ->method('appendBody')
            ->with($renderedData);

        $viewMock = $this->getMock('Magento\Ui\Form\Field', ['render'], [], '', false);
        $viewMock->expects($this->once())
            ->method('render')
            ->willReturn($renderedData);
        $this->uiFactoryMock->expects($this->once())
            ->method('createUiComponent')
            ->willReturn($viewMock);

        $this->assertNull($this->render->execute());
    }
}
