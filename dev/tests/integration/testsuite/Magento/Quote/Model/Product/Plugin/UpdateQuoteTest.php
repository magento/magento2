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
use Magento\Catalog\Api\Data\TierPriceInterfaceFactory;
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
     * @var TierPriceInterfaceFactory
     */
    private $tierPriceFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $objectManager = Bootstrap::getObjectManager();
        $this->getQuoteByReservedOrderId = $objectManager->get(GetQuoteByReservedOrderId::class);
        $this->tierPriceStorage = $objectManager->get(TierPriceStorageInterface::class);
        $this->tierPriceFactory = $objectManager->get(TierPriceInterfaceFactory::class);
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
        $quoteItem->setProductId($product->getRowId());
        $quote->save();

        $tierPrice = $this->tierPriceFactory->create();
        $tierPrice->setPrice($product->getPrice());
        $tierPrice->setPriceType('fixed');
        $tierPrice->setWebsiteId(0);
        $tierPrice->setSku($product->getSku());
        $tierPrice->setCustomerGroup('ALL GROUPS');
        $tierPrice->setQuantity(1);
        $this->tierPriceStorage->update([$tierPrice]);

        $quote = $this->getQuoteByReservedOrderId->execute($quoteId);
        $this->assertNotEmpty($quote->getTriggerRecollect());
        $this->assertTrue((bool)$quote->getTriggerRecollect());
    }
}
