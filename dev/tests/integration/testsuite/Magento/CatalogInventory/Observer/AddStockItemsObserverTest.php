<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Observer;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ResourceModel\Quote\Item\Collection;
use Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class AddStockItemsObserverTest extends TestCase
{
    /**
     * Test addStockItemsObserver add stock items to products as extension attributes in quote item collection.
     *
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testAddStockItemsToProductCollection()
    {
        $quote = Bootstrap::getObjectManager()->create(Quote::class);
        $quote->load('test01', 'reserved_order_id');
        /** @var CollectionFactory $collectionFactory */
        $collectionFactory = Bootstrap::getObjectManager()->create(CollectionFactory::class);
        /** @var Collection $collection */
        $collection = $collectionFactory->create();
        $collection->setQuote($quote);
        /** @var Quote\Item $quoteItem */
        foreach ($collection->getItems() as $quoteItem) {
            self::assertNotEmpty($quoteItem->getProduct()->getExtensionAttributes()->getStockItem());
            self::assertInstanceOf(
                StockItemInterface::class,
                $quoteItem->getProduct()->getExtensionAttributes()->getStockItem()
            );
        }
    }
}
