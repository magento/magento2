<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Test\Integration\SalesQuoteItem;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
use Magento\InventoryReservationsApi\Model\CleanupReservationsInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddSalesQuoteItemOnDefaultStockTest extends TestCase
{
    /**
     * @var CleanupReservationsInterface
     */
    private $cleanupReservations;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->defaultStockProvider = Bootstrap::getObjectManager()->get(DefaultStockProviderInterface::class);
        $this->cleanupReservations = Bootstrap::getObjectManager()->get(CleanupReservationsInterface::class);
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $this->cleanupReservations->execute();
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     */
    public function testAddOutOfStockProductToQuote()
    {
        $productSku = 'SKU-1';
        $productQty = 6;

        $product = $this->getProductBySku($productSku);
        $quote = $this->getQuote();

        self::expectException(LocalizedException::class);
        $quote->addProduct($product, $productQty);
        $quoteItemCount = count($quote->getAllItems());
        self::assertEquals(0, $quoteItemCount);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     */
    public function testAddInStockProductToQuote()
    {
        $productSku = 'SKU-1';
        $productQty = 4;
        $expectedQtyInCart = 4;

        $product = $this->getProductBySku($productSku);
        $quote = $this->getQuote();

        $quote->addProduct($product, $productQty);

        /** @var CartItemInterface $quoteItem */
         $quoteItem = current($quote->getAllItems());
         self::assertEquals($expectedQtyInCart, $quoteItem->getQty());
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     */
    public function testAddProductToQuoteMultipleTimes()
    {
        $productSku = 'SKU-1';
        $productQty1 = 3;
        $productQty2 = 2;
        $productQty3 = 3;
        $expectedQtyInCart1 = 3;
        $expectedQtyInCart2 = 5;
        $expectedQtyInCart3 = 5;

        $product = $this->getProductBySku($productSku);
        $quote = $this->getQuote();

        //(5.5 - 3) 2.5 in stock
        $quote->addProduct($product, $productQty1);
        /** @var CartItemInterface $quoteItem */
        $quoteItem = current($quote->getAllItems());
        self::assertEquals($expectedQtyInCart1, $quoteItem->getQty());

        //(2.5 - 2) 0.5 in stock
        $quote->addProduct($product, $productQty2);
        /** @var CartItemInterface $quoteItem */
        $quoteItem = current($quote->getAllItems());
        self::assertEquals($expectedQtyInCart2, $quoteItem->getQty());

        //(0.5 - 3) -2.5 out of stock
        self::expectException(LocalizedException::class);
        $quote->addProduct($product, $productQty3);
        /** @var CartItemInterface $quoteItem */
        $quoteItem = current($quote->getAllItems());
        self::assertEquals($expectedQtyInCart3, $quoteItem->getQty());
    }

    /**
     * @param string $sku
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    private function getProductBySku(string $sku): ProductInterface
    {
        return $this->productRepository->get($sku);
    }

    /**
     * @return Quote
     */
    private function getQuote(): Quote
    {
        return Bootstrap::getObjectManager()->create(
            Quote::class,
            [
                'data' => [
                    'store_id' => 1,
                    'is_active' => 0,
                    'is_multi_shipping' => 0,
                    'id' => 1
                ]
            ]
        );
    }

    protected function tearDown()
    {
        $this->cleanupReservations->execute();
    }
}
