<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Pdf;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Pdf\Config
     */
    protected $_model;

    /**
     * @var \Magento\Framework\Config\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dataStorage;

    protected function setUp()
    {
        $this->_dataStorage = $this->createMock(\Magento\Framework\Config\Data::class);
        $this->_model = new \Magento\Sales\Model\Order\Pdf\Config($this->_dataStorage);
    }

    public function testGetRenderersPerProduct()
    {
        $configuration = ['product_type_one' => 'Renderer_One', 'product_type_two' => 'Renderer_Two'];
        $this->_dataStorage->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            "renderers/page_type",
            []
        )->will(
            $this->returnValue($configuration)
        );

        $this->assertSame($configuration, $this->_model->getRenderersPerProduct('page_type'));
    }

    public function testGetTotals()
    {
        $configuration = ['total1' => ['title' => 'Title1'], 'total2' => ['title' => 'Title2']];

        $this->_dataStorage->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            'totals',
            []
        )->will(
            $this->returnValue($configuration)
        );

        $this->assertSame($configuration, $this->_model->getTotals());
    }
}
