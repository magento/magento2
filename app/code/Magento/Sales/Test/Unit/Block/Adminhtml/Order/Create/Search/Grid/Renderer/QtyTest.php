<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Block\Adminhtml\Order\Create\Search\Grid\Renderer;

class QtyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer\Qty
     */
    protected $renderer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $rowMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $typeConfigMock;

    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->rowMock = $this->getMock('Magento\Framework\DataObject', ['getTypeId', 'getIndex'], [], '', false);
        $this->typeConfigMock = $this->getMock('Magento\Catalog\Model\ProductTypes\ConfigInterface');
        $this->renderer = $helper->getObject(
            'Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer\Qty',
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
        )->will(
            $this->returnValue(true)
        );
        $this->rowMock->expects($this->once())->method('getTypeId')->will($this->returnValue('id'));
        $columnMock = $this->getMock(
            'Magento\Backend\Block\Widget\Grid\Column',
            ['getInlineCss', 'getId'],
            [],
            '',
            false
        );
        $this->renderer->setColumn($columnMock);

        $columnMock->expects($this->once())->method('getId')->will($this->returnValue('id_name'));
        $columnMock->expects($this->once())->method('getInlineCss')->will($this->returnValue('inline_css'));

        $this->assertEquals($expected, $this->renderer->render($this->rowMock));
    }
}
