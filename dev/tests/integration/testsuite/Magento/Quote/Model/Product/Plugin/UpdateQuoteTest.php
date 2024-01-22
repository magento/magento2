<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Product\Plugin;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Quote\Model\GetQuoteByReservedOrderId;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Quote\Api\Data\CartItemInterfaceFactory;
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

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var CartItemInterfaceFactory */
    private $itemFactory;

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
        $this->itemFactory = $objectManager->get(CartItemInterfaceFactory::class);
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
    }

    /**
     * Test to update the column trigger_recollect is 1 from quote table.
     *
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @return void
     */
    public function testUpdateQuoteRecollectAfterChangeProductPrice(): void
    {
        $quoteId = 'test01';
        $productSku = 'simple';
        $quote = $this->getQuoteByReservedOrderId->execute($quoteId);

        $quoteItem = $this->itemFactory->create();

        $product = $this->productRepository->get($productSku);

        $quoteItem->setProduct($product);
        $quoteItem->setProductId($product->getRowId());

        $quote->addItem($quoteItem);
        $quote->setTriggerRecollect(0);
        $quote->save();

        $this->assertNotNull($quote);
        $this->assertFalse((bool)$quote->getTriggerRecollect());

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
