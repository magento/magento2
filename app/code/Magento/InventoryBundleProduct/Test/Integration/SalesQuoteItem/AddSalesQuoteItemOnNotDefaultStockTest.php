<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryBundleProduct\Test\Integration\SalesQuoteItem;

use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventoryReservationsApi\Model\CleanupReservationsInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class AddSalesQuoteItemOnNotDefaultStockTest extends TestCase
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var CleanupReservationsInterface
     */
    private $cleanupReservations;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->cleanupReservations = Bootstrap::getObjectManager()->get(CleanupReservationsInterface::class);
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $this->stockRepository = Bootstrap::getObjectManager()->get(StockRepositoryInterface::class);
        $this->storeRepository = Bootstrap::getObjectManager()->get(StoreRepositoryInterface::class);
        $this->storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
        $this->dataObjectFactory = Bootstrap::getObjectManager()->get(DataObjectFactory::class);
        $this->cleanupReservations->execute();
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryBundleProduct/Test/_files/product_bundle.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryBundleProduct/Test/_files/source_items_bundle.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @magentoDbIsolation disabled
     */
    public function testAddInStockProductToQuote()
    {
        $this->markTestSkipped('Waiting for bundle product on not default stock support');

        $productSku = 'bundle';
        $productQty = 4;
        $expectedQtyInCart = 4;

        $product = $this->getProductBySku($productSku);
        $quote = $this->getQuote();

        $quote->addProduct($product, $this->getBuyRequest($product, $productQty));

        /** @var CartItemInterface $quoteItem */
        $quoteItem = current($quote->getAllItems());
        self::assertEquals($expectedQtyInCart, $quoteItem->getQty());
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryBundleProduct/Test/_files/product_bundle.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryBundleProduct/Test/_files/source_items_bundle.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryBundleProduct/Test/_files/set_product_bundle_out_of_stock.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @magentoDbIsolation disabled
     */
    public function testAddOutOffStockProductToQuote()
    {
        $this->markTestSkipped('Waiting for bundle product on not default stock support');

        $productSku = 'bundle';
        $productQty = 6;
        $expectedItemsInCart = 0;

        $product = $this->getProductBySku($productSku);
        $quote = $this->getQuote();

        self::expectException(LocalizedException::class);
        $quote->addProduct($product, $this->getBuyRequest($product, $productQty));
        $quoteItemCount = count($quote->getAllItems());
        self::assertEquals($expectedItemsInCart, $quoteItemCount);
    }

    /**
     * @param string $sku
     *
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    private function getProductBySku(string $sku): ProductInterface
    {
        return $this->productRepository->get($sku, false, 'store_for_us_website');
    }

    /**
     * @return Quote
     * @throws NoSuchEntityException
     */
    private function getQuote(): Quote
    {
        $storeCode = 'store_for_us_website';
        /** @var StoreInterface $store */
        $store = $this->storeRepository->get($storeCode);
        $this->storeManager->setCurrentStore($storeCode);

        return Bootstrap::getObjectManager()->create(
            Quote::class,
            [
                'data' => [
                    'store_id' => $store->getId(),
                    'is_active' => 0,
                    'is_multi_shipping' => 0,
                    'id' => 1
                ]
            ]
        );
    }

    /**
     * @param ProductInterface $product
     * @param float $productQty
     *
     * @return DataObject
     * @throws LocalizedException
     */
    private function getBuyRequest(ProductInterface $product, float $productQty): DataObject
    {
        /** @var Type $bundleProduct */
        $bundleProduct = $product->getTypeInstance();
        $selectionCollection = $bundleProduct->getSelectionsCollection(
            $bundleProduct->getOptionsIds($product),
            $product
        );
        $bundleOptions = $bundleProduct->getOptions($product);
        $option = current($bundleOptions);

        $selections = array_map(
            function ($selection) {
                return $selection['selection_id'];
            },
            $selectionCollection->getData()
        );

        return $this->dataObjectFactory->create(
            [
                'product' => $option->getParentId(),
                'bundle_option' => [$option->getOptionId() => [$selections]],
                'qty' => $productQty
            ]
        );
    }

    protected function tearDown()
    {
        $this->cleanupReservations->execute();
    }
}
