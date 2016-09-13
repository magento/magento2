<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Block\Adminhtml\Order\Create;

use Magento\Catalog\Pricing\Price\FinalPrice;

class AbstractCreateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \Magento\Framework\Pricing\PriceInfo\Base|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceInfoMock;

    /**
     * @var \Magento\Downloadable\Pricing\Price\LinkPrice|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkPriceMock;

    protected function setUp()
    {
        $this->model = $this->getMockBuilder(\Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate::class)
            ->setMethods(['convertPrice'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->priceInfoMock = $this->getMockBuilder(\Magento\Framework\Pricing\PriceInfo\Base::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->linkPriceMock = $this->getMockBuilder(\Magento\Downloadable\Pricing\Price\LinkPrice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productMock->expects($this->any())
            ->method('getPriceInfo')
            ->willReturn($this->priceInfoMock);
    }

    public function testGetItemPrice()
    {
        $price = 5.6;
        $resultPrice = 9.3;

        $this->linkPriceMock->expects($this->once())
            ->method('getValue')
            ->willReturn($price);
        $this->priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->with(FinalPrice::PRICE_CODE)
            ->willReturn($this->linkPriceMock);
        $this->model->expects($this->once())
            ->method('convertPrice')
            ->with($price)
            ->willReturn($resultPrice);
        $this->assertEquals($resultPrice, $this->model->getItemPrice($this->productMock));
    }
}
