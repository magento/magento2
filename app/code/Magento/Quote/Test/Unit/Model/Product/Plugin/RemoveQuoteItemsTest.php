<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\Product\Plugin;

class RemoveQuoteItemsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Model\Product\Plugin\RemoveQuoteItems
     */
    private $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Quote\Model\Product\QuoteItemsCleanerInterface
     */
    private $quoteItemsCleanerMock;

    protected function setUp(): void
    {
        $this->quoteItemsCleanerMock = $this->createMock(
            \Magento\Quote\Model\Product\QuoteItemsCleanerInterface::class
        );
        $this->model = new \Magento\Quote\Model\Product\Plugin\RemoveQuoteItems($this->quoteItemsCleanerMock);
    }

    public function testAfterDelete()
    {
        $productResourceMock = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product::class);
        $productMock = $this->createMock(\Magento\Catalog\Api\Data\ProductInterface::class);

        $this->quoteItemsCleanerMock->expects($this->once())->method('execute')->with($productMock);
        $result = $this->model->afterDelete($productResourceMock, $productResourceMock, $productMock);
        $this->assertEquals($result, $productResourceMock);
    }
}
