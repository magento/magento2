<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Block\Adminhtml\Order\Create\Search\Grid\Renderer;

use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer\Qty;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class QtyTest extends TestCase
{
    /**
     * @var Qty
     */
    protected $renderer;

    /**
     * @var MockObject
     */
    protected $rowMock;

    /**
     * @var MockObject
     */
    protected $typeConfigMock;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);

        $this->rowMock = $this->getMockBuilder(DataObject::class)
            ->addMethods(['getTypeId', 'getIndex'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->typeConfigMock = $this->getMockForAbstractClass(ConfigInterface::class);
        $this->renderer = $helper->getObject(
            Qty::class,
            ['typeConfig' => $this->typeConfigMock]
        );
    }

    public function testRender()
    {
        $expected = '<input type="text" name="id_name" value="" disabled="disabled" ' .
            'class="input-text admin__control-text inline_css input-inactive" />';
        $this->typeConfigMock->expects(
            $this->any()
        )->method(
            'isProductSet'
        )->with(
            'id'
        )->willReturn(
            true
        );
        $this->rowMock->expects($this->once())->method('getTypeId')->willReturn('id');
        $columnMock = $this->getMockBuilder(Column::class)
            ->addMethods(['getInlineCss'])
            ->onlyMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->renderer->setColumn($columnMock);

        $columnMock->expects($this->once())->method('getId')->willReturn('id_name');
        $columnMock->expects($this->once())->method('getInlineCss')->willReturn('inline_css');

        $this->assertEquals($expected, $this->renderer->render($this->rowMock));
    }
}
