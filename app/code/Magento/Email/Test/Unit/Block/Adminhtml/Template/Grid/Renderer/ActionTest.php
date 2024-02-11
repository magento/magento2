<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Email\Test\Unit\Block\Adminhtml\Template\Grid\Renderer;

use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Action;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Action
 */
class ActionTest extends TestCase
{
    /**
     * @var Action
     */
    protected $action;

    /**
     * @var MockObject
     */
    protected $columnMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->columnMock = $this->getMockBuilder(Column::class)
            ->disableOriginalConstructor()
            ->addMethods(['setActions', 'getActions'])
            ->getMock();
        $this->action = $objectManager->getObject(Action::class);
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
        $row = new DataObject();
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
        $row = new DataObject();
        $row->setId(1);
        $this->assertStringContainsString('admin__control-select', $this->action->render($row));
    }
}
