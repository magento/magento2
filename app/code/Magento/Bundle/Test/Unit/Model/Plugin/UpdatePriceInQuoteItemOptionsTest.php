<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Model\Plugin;

use Magento\Bundle\Model\Plugin\UpdatePriceInQuoteItemOptions;
use Magento\Catalog\Model\Product;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Quote\Model\Quote\Item\Option;

/**
 * Test for Magento\Bundle\Model\Plugin\UpdatePriceInQuoteItemOptions class.
 */
class UpdatePriceInQuoteItemOptionsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    /**
     * @var QuoteItem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectMock;

    /**
     * @var AbstractItem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultMock;

    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productMock;

    /**
     * @var Option|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteItemOptionMock;

    /**
     * @var UpdatePriceInQuoteItemOptions
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->serializerMock = $this->createMock(SerializerInterface::class);
        $this->subjectMock = $this->createMock(QuoteItem::class);
        $this->resultMock = $this->createMock(AbstractItem::class);
        $this->productMock = $this->createMock(Product::class);
        $this->quoteItemOptionMock = $this->createMock(Option::class);

        $this->model = new UpdatePriceInQuoteItemOptions($this->serializerMock);
    }

    /**
     * @return void
     */
    public function testAfterCalcRowTotalWithBundleOption()
    {
        $bundleAttributeValue = '{"price":100,"qty":1,"option_label":"option1","option_id":"1"}';
        $parsedValue = [
            'price' => 100,
            'qty' => 1,
            'option_label' => 'option1',
            'option_id' => "1",
        ];

        $this->resultMock->expects($this->once())
            ->method('getProduct')
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())
            ->method('getCustomOption')
            ->with('bundle_selection_attributes')
            ->willReturn($this->quoteItemOptionMock);
        $this->resultMock->expects($this->once())
            ->method('getPrice')
            ->willReturn(100);
        $this->resultMock->expects($this->once())
            ->method('getQty')
            ->willReturn(1);
        $this->quoteItemOptionMock->expects($this->once())
            ->method('getValue')
            ->willReturn($bundleAttributeValue);
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($bundleAttributeValue)
            ->willReturn($parsedValue);
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($parsedValue)
            ->willReturn($bundleAttributeValue);

        $this->model->afterCalcRowTotal($this->subjectMock, $this->resultMock);
    }

    /**
     * @return void
     */
    public function testAfterCalcRowTotalWithoutBundleOption()
    {
        $this->resultMock->expects($this->once())
            ->method('getProduct')
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())
            ->method('getCustomOption')
            ->with('bundle_selection_attributes')
            ->willReturn(null);
        $this->resultMock->expects($this->never())
            ->method('getPrice');
        $this->resultMock->expects($this->never())
            ->method('getQty');
        $this->quoteItemOptionMock->expects($this->never())
            ->method('getValue');
        $this->serializerMock->expects($this->never())
            ->method('unserialize');
        $this->serializerMock->expects($this->never())
            ->method('serialize');

        $this->model->afterCalcRowTotal($this->subjectMock, $this->resultMock);
    }
}
