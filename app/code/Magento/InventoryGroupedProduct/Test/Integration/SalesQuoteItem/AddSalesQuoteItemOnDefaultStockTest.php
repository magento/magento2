<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryGroupedProduct\Test\Integration\SalesQuoteItem;

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
use Magento\Framework\DataObject\Factory as DataObjectFactory;

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
        $this->cleanupReservations->execute();
        $this->dataObjectFactory = Bootstrap::getObjectManager()->get(DataObjectFactory::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryGroupedProduct/Test/_files/default_stock_grouped_products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     */
    public function testAddOutOfStockProductToQuote()
    {
        $productSku = 'grouped_out_of_stock';
        $associatedProductQtys = [
            11 => 3,
            22 => 5
        ];

        $product = $this->getProductBySku($productSku);
        $request = $this->dataObjectFactory->create(
            [
                'product' => $product->getId(),
                'super_group' => $associatedProductQtys
            ]
        );
        $quote = $this->getQuote();

        self::expectException(LocalizedException::class);
        $quote->addProduct($product, $request);
        $quoteItemCount = count($quote->getAllItems());
        self::assertEquals(0, $quoteItemCount);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryGroupedProduct/Test/_files/default_stock_grouped_products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     */
    public function testAddInStockProductToQuote()
    {
        $productSku = 'grouped_in_stock';
        $associatedProductQtys = [
            11 => 3,
            22 => 5
        ];
        $expectedQtyInCart = [
            11 => 3,
            22 => 5
        ];

        $product = $this->getProductBySku($productSku);
        $request = $this->dataObjectFactory->create(
            [
                'product' => $product->getId(),
                'super_group' => $associatedProductQtys
            ]
        );

        $quote = $this->getQuote();
        $quote->addProduct($product, $request);

        /** @var CartItemInterface $quoteItem */
        foreach ($quote->getAllItems() as $quoteItem) {
            self::assertEquals($expectedQtyInCart[$quoteItem->getProductId()], $quoteItem->getQty());
        }
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryGroupedProduct/Test/_files/default_stock_grouped_products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     */
    public function testAddProductToQuoteMultipleTimes()
    {
        $productSku = 'grouped_in_stock';
        $associatedProductQtys1 = [
            11 => 65,
            22 => 83
        ];
        $associatedProductQtys2 = [
            11 => 27,
            22 => 11
        ];
        $associatedProductQtys3 = [
            11 => 9,
            22 => 10
        ];
        $expectedQtyInCart1 = [
            11 => 65,
            22 => 83
        ];
        $expectedQtyInCart2 = [
            11 => 92,
            22 => 94
        ];
        $expectedQtyInCart3 = [
            11 => 92,
            22 => 94
        ];

        $product = $this->getProductBySku($productSku);
        $quote = $this->getQuote();

        // ID:11 => (100 - 65) 35 in stock
        // ID:22 => (100 - 83) 17 in stock
        $request1 = $this->dataObjectFactory->create(
            [
                'product' => $product->getId(),
                'super_group' => $associatedProductQtys1
            ]
        );
        $quote->addProduct($product, $request1);
        /** @var CartItemInterface $quoteItem */
        foreach ($quote->getAllItems() as $quoteItem) {
            self::assertEquals($expectedQtyInCart1[$quoteItem->getProductId()], $quoteItem->getQty());
        }

        // ID:11 => (35 - 27) 8 in stock
        // ID:22 => (17 - 11) 6 in stock
        $request2 = $this->dataObjectFactory->create(
            [
                'product' => $product->getId(),
                'super_group' => $associatedProductQtys2
            ]
        );
        $quote->addProduct($product, $request2);
        /** @var CartItemInterface $quoteItem */
        foreach ($quote->getAllItems() as $quoteItem) {
            self::assertEquals($expectedQtyInCart2[$quoteItem->getProductId()], $quoteItem->getQty());
        }

        // ID:11 => (8 - 9) -1 out of stock
        // ID:22 => (6 - 10) -4 out of stock
        $request3 = $this->dataObjectFactory->create(
            [
                'product' => $product->getId(),
                'super_group' => $associatedProductQtys3
            ]
        );
        self::expectException(LocalizedException::class);
        $quote->addProduct($product, $request3);
        /** @var CartItemInterface $quoteItem */
        foreach ($quote->getAllItems() as $quoteItem) {
            self::assertEquals($expectedQtyInCart3[$quoteItem->getProductId()], $quoteItem->getQty());
        }
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
