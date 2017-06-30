<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Test\Unit\Block\Adminhtml\Template\Grid\Renderer;

/**
 * @covers Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Action
 */
class ActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Action
     */
    protected $action;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $columnMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->columnMock = $this->getMockBuilder(\Magento\Backend\Block\Widget\Grid\Column::class)
            ->disableOriginalConstructor()
            ->setMethods(['setActions', 'getActions'])
            ->getMock();
        $this->action = $objectManager->getObject(\Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Action::class);
    }

    /**
     * @covers \Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Action::render
     */
    public function testRenderNoActions()
    {
        $this->columnMock->expects($this->once())
            ->method('setActions');
        $this->columnMock->expects($this->once())
            ->method('getActions')
            ->willReturn('');
        $this->action->setColumn($this->columnMock);
        $row = new \Magento\Framework\DataObject();
        $this->assertEquals('&nbsp;', $this->action->render($row));
    }

    /**
     * @covers \Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Action::render
     */
    public function testRender()
    {
        $this->columnMock->expects($this->once())
            ->method('setActions');
        $this->columnMock->expects($this->once())
            ->method('getActions')
            ->willReturn(['url', 'popup', 'caption']);
        $this->action->setColumn($this->columnMock);
        $row = new \Magento\Framework\DataObject();
        $row->setId(1);
        $this->assertContains('admin__control-select', $this->action->render($row));
    }
}
