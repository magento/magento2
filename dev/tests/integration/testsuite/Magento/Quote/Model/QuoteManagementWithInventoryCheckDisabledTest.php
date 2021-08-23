<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Quote\Model\GetQuoteByReservedOrderId;
use PHPUnit\Framework\TestCase;

class QuoteManagementWithInventoryCheckDisabledTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /** @var ProductRepositoryInterface $productRepository */
    private $productRepository;

    /**
     * @var GetQuoteByReservedOrderId
     */
    private $getQuoteByReservedOrderId;

    /**
     * @var CartManagementInterface
     */
    private $cartManagement;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->cartManagement = $this->objectManager->get(CartManagementInterface::class);
        $this->getQuoteByReservedOrderId = $this->objectManager->get(GetQuoteByReservedOrderId::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/quote_with_purchase_order.php
     * @magentoConfigFixture cataloginventory/options/enable_inventory_check 0
     * @return void
     */
    public function testSaveWithZeroQuantityAndInventoryCheckDisabled()
    {
        $poNumber = '12345678';
        $quote = $this->getQuoteByReservedOrderId->execute('test_order_1');
        $quote->getPayment()->setPoNumber($poNumber);
        $quote->collectTotals()->save();

        /** @var ProductInterface $product */
        $product = $this->productRepository->get($quote->getItems()[0]->getSku(), false, null, true);

        $this->productRepository->save($product);

        $stockItem = $product->getExtensionAttributes()->getStockItem();
        $stockItem->setQty(0);
        $stockItem->setIsInStock(0);

        /** @var StockItemRepositoryInterface $stockItemRepository */
        $stockItemRepository = $this->objectManager->get(StockItemRepositoryInterface::class);
        $stockItemRepository->save($stockItem);

        $this->expectExceptionObject(
            new LocalizedException(__('Some of the products are out of stock.'))
        );
        $this->cartManagement->placeOrder($quote->getId());
    }


    /**
     * @magentoDataFixture Magento/Sales/_files/quote_with_purchase_order.php
     * @magentoConfigFixture cataloginventory/options/enable_inventory_check 0
     * @return void
     */
    public function testSaveWithPositiveQuantityAndInventoryCheckDisabled()
    {
        $poNumber = '12345678';
        $quote = $this->getQuoteByReservedOrderId->execute('test_order_1');
        $quote->getPayment()->setPoNumber($poNumber);
        $quote->collectTotals()->save();

        /** @var ProductInterface $product */
        $product = $this->productRepository->get($quote->getItems()[0]->getSku(), false, null, true);

        $this->productRepository->save($product);

        $stockItem = $product->getExtensionAttributes()->getStockItem();
        $stockItem->setQty(100);
        $stockItem->setIsInStock(0);

        /** @var StockItemRepositoryInterface $stockItemRepository */
        $stockItemRepository = $this->objectManager->get(StockItemRepositoryInterface::class);
        $stockItemRepository->save($stockItem);

        $this->expectExceptionObject(
            new LocalizedException(__('Some of the products are out of stock.'))
        );
        $this->cartManagement->placeOrder($quote->getId());
    }
}
