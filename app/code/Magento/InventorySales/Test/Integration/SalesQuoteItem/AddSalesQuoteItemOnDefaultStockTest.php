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
use Magento\InventoryCatalog\Api\DefaultStockProviderInterface;
use Magento\InventoryReservations\Model\CleanupReservationsInterface;
use Magento\InventoryReservations\Model\ReservationBuilderInterface;
use Magento\InventoryReservationsApi\Api\AppendReservationsInterface;
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
     * @var ReservationBuilderInterface
     */
    private $reservationBuilder;

    /**
     * @var AppendReservationsInterface
     */
    private $appendReservations;

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

        $this->reservationBuilder = Bootstrap::getObjectManager()->get(ReservationBuilderInterface::class);
        $this->defaultStockProvider = Bootstrap::getObjectManager()->get(DefaultStockProviderInterface::class);
        $this->appendReservations = Bootstrap::getObjectManager()->get(AppendReservationsInterface::class);
        $this->cleanupReservations = Bootstrap::getObjectManager()->get(CleanupReservationsInterface::class);
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     */
    public function testAddOutOfStockProductToQuote()
    {
        $this->markTestSkipped(
            'Fix add to Cart Integration Test https://github.com/magento-engcom/msi/issues/688'
        );
        $productSku = 'SKU-1';
        $productQty = 5.5;
        // set reservation before (reserve -1.5 units, last 4)
        $this->appendReservation($productSku, -1.5);
        $product = $this->getProductBySku($productSku);
        $quote = $this->getQuote();

        self::expectException(LocalizedException::class);
        $quote->addProduct($product, $productQty);
        $quoteItemCount = count($quote->getAllItems());
        self::assertEquals(0, $quoteItemCount);

        //cleanup
        $this->appendReservation($productSku, 1.5);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     */
    public function testAddInStockProductToQuote()
    {
        $this->markTestSkipped(
            'Fix add to Cart Integration Test https://github.com/magento-engcom/msi/issues/688'
        );
        $productSku = 'SKU-1';
        $productQty = 4;
        $expectedQtyInCart = 4;
        // set reservation before (reserve -1.5 units, last 4)
        $this->appendReservation($productSku, -1.5);
        $product = $this->getProductBySku($productSku);
        $quote = $this->getQuote();

        $quote->addProduct($product, $productQty);

        /** @var CartItemInterface $quoteItem */
         $quoteItem = current($quote->getAllItems());
         self::assertEquals($expectedQtyInCart, $quoteItem->getQty());

        //cleanup
        $this->appendReservation($productSku, 1.5);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     */
    public function testAddProductToQuoteMultipleTimes()
    {
        $this->markTestSkipped(
            'Fix add to Cart Integration Test https://github.com/magento-engcom/msi/issues/688'
        );
        $productSku = 'SKU-1';
        $productQty1 = 1;
        $productQty2 = 2.5;
        $productQty3 = 3.2;
        $expectedQtyInCart1 = 1;
        $expectedQtyInCart2 = 3.5;
        $expectedQtyInCart3 = 3.5;
        // set reservation before (reserve -1.5 units, last 4)
        $this->appendReservation($productSku, -1.5);
        $product = $this->getProductBySku($productSku);
        $quote = $this->getQuote();

        //(4 - 1) 3 in source
        $quote->addProduct($product, $productQty1);
        /** @var CartItemInterface $quoteItem */
        $quoteItem = current($quote->getAllItems());
        self::assertEquals($expectedQtyInCart1, $quoteItem->getQty());

        //(3 - 2.5) 0.5 in source
        $quote->addProduct($product, $productQty2);
        /** @var CartItemInterface $quoteItem */
        $quoteItem = current($quote->getAllItems());
        self::assertEquals($expectedQtyInCart2, $quoteItem->getQty());

        //(0.5 - 3.5) -3 out of stock
        self::expectException(LocalizedException::class);
        $quote->addProduct($product, $productQty3);
        /** @var CartItemInterface $quoteItem */
        $quoteItem = current($quote->getAllItems());
        self::assertEquals($expectedQtyInCart3, $quoteItem->getQty());

        //cleanup
        $this->appendReservation($productSku, 1.5);
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
     * @param string $productSku
     * @param float $qty
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Validation\ValidationException
     */
    private function appendReservation(string $productSku, float $qty)
    {
        $this->appendReservations->execute([
            $this->reservationBuilder->setStockId(
                $this->defaultStockProvider->getId()
            )->setSku($productSku)->setQuantity($qty)->build(),
        ]);
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
