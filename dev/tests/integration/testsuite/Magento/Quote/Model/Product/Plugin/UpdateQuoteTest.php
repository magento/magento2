<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Product\Plugin;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Quote\Model\GetQuoteByReservedOrderId;
use Magento\Catalog\Api\TierPriceStorageInterface;
use Magento\Catalog\Api\Data\TierPriceInterface;
use PHPUnit\Framework\TestCase;

/**
 * Tests for update quote plugin.
 */
class UpdateQuoteTest extends TestCase
{
    /**
     * @var GetQuoteByReservedOrderId
     */
    private $getQuoteByReservedOrderId;

    /**
     * @var TierPriceStorageInterface
     */
    private $tierPriceStorage;

    /**
     * @var TierPriceInterface
     */
    private $tierPrice;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $objectManager = Bootstrap::getObjectManager();
        $this->getQuoteByReservedOrderId = $objectManager->get(GetQuoteByReservedOrderId::class);
        $this->tierPriceStorage = $objectManager->get(TierPriceStorageInterface::class);
        $this->tierPrice = $objectManager->get(TierPriceInterface::class);
    }

    /**
     * Test to update the column trigger_recollect is 1 from quote table.
     *
     * @magentoDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @return void
     */
    public function testUpdateQuoteRecollectAfterChangeProductPrice(): void
    {
        $quoteId = 'test_order_with_simple_product_without_address';
        $quote = $this->getQuoteByReservedOrderId->execute($quoteId);
        $this->assertNotNull($quote);
        $this->assertFalse((bool)$quote->getTriggerRecollect());
        $this->assertNotEmpty($quote->getItems());
        $quoteItem = current($quote->getItems());
        $product = $quoteItem->getProduct();

        $this->tierPrice->setPrice($product->getPrice());
        $this->tierPrice->setPriceType('fixed');
        $this->tierPrice->setWebsiteId(0);
        $this->tierPrice->setSku($product->getSku());
        $this->tierPrice->setCustomerGroup('ALL GROUPS');
        $this->tierPrice->setQuantity(1);
        $this->tierPriceStorage->update([$this->tierPrice]);

        /** @var QuoteResource $quoteResource */
        $quoteResource = $quote->getResource();
        /** @var AdapterInterface $connection */
        $connection = $quoteResource->getConnection();
        $select = $connection->select()
            ->from(
                $quoteResource->getTable('quote'),
                ['trigger_recollect']
            )->where(
                "reserved_order_id = 'test_order_with_simple_product_without_address'"
            );

        $quoteRow = $connection->fetchRow($select);
        $this->assertNotEmpty($quoteRow);
        $this->assertTrue((bool)$quoteRow['trigger_recollect']);
    }
}
