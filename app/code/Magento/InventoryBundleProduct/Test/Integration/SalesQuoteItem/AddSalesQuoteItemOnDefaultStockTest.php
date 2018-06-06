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
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
use Magento\InventoryReservationsApi\Model\CleanupReservationsInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

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
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->defaultStockProvider = Bootstrap::getObjectManager()->get(DefaultStockProviderInterface::class);
        $this->cleanupReservations = Bootstrap::getObjectManager()->get(CleanupReservationsInterface::class);
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $this->dataObjectFactory = Bootstrap::getObjectManager()->get(DataObjectFactory::class);
        $this->cleanupReservations->execute();
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryBundleProduct/Test/_files/default_stock_bundle_products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     */
    public function testAddOutOfStockProductToQuote()
    {
        $productSku = 'bundle-product-out-of-stock';
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
     * @magentoDataFixture ../../../../app/code/Magento/InventoryBundleProduct/Test/_files/default_stock_bundle_products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     */
    public function testAddInStockProductToQuote()
    {
        $productSku = 'bundle-product-in-stock';
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
     * @param string $sku
     *
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    private function getProductBySku(string $sku): ProductInterface
    {
        return $this->productRepository->get($sku);
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
