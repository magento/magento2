<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Block\Adminhtml\Widget\Grid\Column\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer\Button;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ButtonTest extends TestCase
{
    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var Escaper|MockObject
     */
    protected $escaperMock;

    /**
     * @var ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var Button
     */
    protected $buttonRenderer;

    protected function setUp(): void
    {
        $this->escaperMock = $this->createMock(Escaper::class);
        $this->escaperMock->expects($this->any())->method('escapeHtml')->willReturnArgument(0);
        $this->contextMock = $this->createPartialMock(Context::class, ['getEscaper']);
        $this->contextMock->expects($this->any())->method('getEscaper')->willReturn($this->escaperMock);

        $this->objectManagerHelper = new ObjectManager($this);
        $this->buttonRenderer = $this->objectManagerHelper->getObject(
            Button::class,
            ['context' => $this->contextMock]
        );
    }

    /**
     * Test the basic render action.
     */
    public function testRender()
    {
        $expectedResult = '<button id="1" type="bigButton">my button</button>';
        $column = $this->getMockBuilder(Column::class)
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

        $object = new DataObject(['name' => 'my button']);
        $actualResult = $this->buttonRenderer->render($object);
        $this->assertEquals($expectedResult, $actualResult);
    }
}
