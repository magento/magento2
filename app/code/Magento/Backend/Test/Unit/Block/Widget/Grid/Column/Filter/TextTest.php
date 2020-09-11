<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Block\Widget\Grid\Column\Filter;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Column\Filter\Text;
use Magento\Framework\DB\Helper;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for \Magento\Backend\Block\Widget\Grid\Column\Filter\Text
 */
class TextTest extends TestCase
{
    /** @var Text*/
    protected $block;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var Context|MockObject */
    protected $context;

    /** @var Helper|MockObject */
    protected $helper;

    /** @var Escaper|MockObject */
    protected $escaper;

    protected function setUp(): void
    {
        $this->context = $this->getMockBuilder(Context::class)
            ->setMethods(['getEscaper'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->escaper = $this->createPartialMock(
            Escaper::class,
            ['escapeHtml', 'escapeHtmlAttr']
        );
        $this->helper = $this->createMock(Helper::class);

        $this->context->expects($this->once())->method('getEscaper')->willReturn($this->escaper);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->block = $this->objectManagerHelper->getObject(
            Text::class,
            [
                'context' => $this->context,
                'resourceHelper' => $this->helper
            ]
        );
    }

    public function testGetHtml()
    {
        $resultHtml = '<input type="text" name="escapedHtml" ' .
            'id="escapedHtml" value="escapedHtml" ' .
            'class="input-text admin__control-text no-changes" data-ui-id="filter-escapedhtml"  />';

        $column = $this->getMockBuilder(Column::class)
            ->setMethods(['getId', 'getHtmlId'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->block->setColumn($column);

        $this->escaper->expects($this->any())->method('escapeHtml')->willReturn('escapedHtml');
        $this->escaper->expects($this->once())
            ->method('escapeHtmlAttr')
            ->willReturnCallback(
                function ($string) {
                    return $string;
                }
            );
        $column->expects($this->any())->method('getId')->willReturn('id');
        $column->expects($this->once())->method('getHtmlId')->willReturn('htmlId');

        $this->assertEquals($resultHtml, $this->block->getHtml());
    }
}
