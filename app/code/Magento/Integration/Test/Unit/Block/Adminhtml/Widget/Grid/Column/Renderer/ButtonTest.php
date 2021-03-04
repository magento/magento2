<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Unit\Block\Adminhtml\Widget\Grid\Column\Renderer;

class ButtonTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Backend\Block\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $escaperMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer\Button
     */
    protected $buttonRenderer;

    protected function setUp(): void
    {
        $this->escaperMock = $this->createMock(\Magento\Framework\Escaper::class);
        $this->escaperMock->expects($this->any())->method('escapeHtml')->willReturnArgument(0);
        $this->contextMock = $this->createPartialMock(\Magento\Backend\Block\Context::class, ['getEscaper']);
        $this->contextMock->expects($this->any())->method('getEscaper')->willReturn($this->escaperMock);

        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->buttonRenderer = $this->objectManagerHelper->getObject(
            \Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer\Button::class,
            ['context' => $this->contextMock]
        );
    }

    /**
     * Test the basic render action.
     */
    public function testRender()
    {
        $expectedResult = '<button id="1" type="bigButton">my button</button>';
        $column = $this->getMockBuilder(\Magento\Backend\Block\Widget\Grid\Column::class)
            ->disableOriginalConstructor()
            ->setMethods(['getType', 'getId', 'getIndex'])
            ->getMock();
        $column->expects($this->any())
            ->method('getType')
            ->willReturn('bigButton');
        $column->expects($this->any())
            ->method('getId')
            ->willReturn('1');
        $this->escaperMock->expects($this->at(0))->method('escapeHtmlAttr')->willReturn('1');
        $this->escaperMock->expects($this->at(1))->method('escapeHtmlAttr')->willReturn('bigButton');
        $column->expects($this->any())
            ->method('getIndex')
            ->willReturn('name');
        $this->buttonRenderer->setColumn($column);

        $object = new \Magento\Framework\DataObject(['name' => 'my button']);
        $actualResult = $this->buttonRenderer->render($object);
        $this->assertEquals($expectedResult, $actualResult);
    }
}
