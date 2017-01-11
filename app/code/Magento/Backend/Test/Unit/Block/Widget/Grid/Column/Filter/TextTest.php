<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Unit\Block\Widget\Grid\Column\Filter;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class TextTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Backend\Block\Widget\Grid\Column\Filter\Text*/
    protected $block;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Backend\Block\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    /** @var \Magento\Framework\DB\Helper|\PHPUnit_Framework_MockObject_MockObject */
    protected $helper;

    /** @var \Magento\Framework\Escaper|\PHPUnit_Framework_MockObject_MockObject */
    protected $escaper;

    protected function setUp()
    {
        $this->context = $this->getMockBuilder(\Magento\Backend\Block\Context::class)
            ->setMethods(['getEscaper'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->escaper = $this->getMock(\Magento\Framework\Escaper::class, ['escapeHtml'], [], '', false);
        $this->helper = $this->getMock(\Magento\Framework\DB\Helper::class, [], [], '', false);

        $this->context->expects($this->once())->method('getEscaper')->willReturn($this->escaper);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->block = $this->objectManagerHelper->getObject(
            \Magento\Backend\Block\Widget\Grid\Column\Filter\Text::class,
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

        $column = $this->getMockBuilder(\Magento\Backend\Block\Widget\Grid\Column::class)
            ->setMethods(['getId', 'getHtmlId'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->block->setColumn($column);

        $this->escaper->expects($this->any())->method('escapeHtml')->willReturn('escapedHtml');
        $column->expects($this->any())->method('getId')->willReturn('id');
        $column->expects($this->once())->method('getHtmlId')->willReturn('htmlId');

        $this->assertEquals($resultHtml, $this->block->getHtml());
    }
}
