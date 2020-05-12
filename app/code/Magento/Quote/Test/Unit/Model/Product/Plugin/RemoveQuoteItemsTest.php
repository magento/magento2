<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Product\Plugin;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Quote\Model\Product\Plugin\RemoveQuoteItems;
use Magento\Quote\Model\Product\QuoteItemsCleanerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RemoveQuoteItemsTest extends TestCase
{
    /**
     * @var RemoveQuoteItems
     */
    private $model;

    /**
     * @var MockObject|QuoteItemsCleanerInterface
     */
    private $quoteItemsCleanerMock;

    protected function setUp(): void
    {
        $this->quoteItemsCleanerMock = $this->createMock(
            QuoteItemsCleanerInterface::class
        );
        $this->model = new RemoveQuoteItems($this->quoteItemsCleanerMock);
    }

    public function testAfterDelete()
    {
        $productResourceMock = $this->createMock(Product::class);
        $productMock = $this->getMockForAbstractClass(ProductInterface::class);

        $this->quoteItemsCleanerMock->expects($this->once())->method('execute')->with($productMock);
        $result = $this->model->afterDelete($productResourceMock, $productResourceMock, $productMock);
        $this->assertEquals($result, $productResourceMock);
    }
}
