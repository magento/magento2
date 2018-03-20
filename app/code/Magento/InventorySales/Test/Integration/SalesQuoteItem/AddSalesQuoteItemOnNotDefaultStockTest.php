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
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventoryReservations\Model\CleanupReservationsInterface;
use Magento\InventoryReservations\Model\ReservationBuilderInterface;
use Magento\InventoryReservationsApi\Api\AppendReservationsInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddSalesQuoteItemOnNotDefaultStockTest extends TestCase
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
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->cleanupReservations = Bootstrap::getObjectManager()->get(CleanupReservationsInterface::class);
        $this->reservationBuilder = Bootstrap::getObjectManager()->get(ReservationBuilderInterface::class);
        $this->appendReservations = Bootstrap::getObjectManager()->get(AppendReservationsInterface::class);
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $this->stockRepository = Bootstrap::getObjectManager()->get(StockRepositoryInterface::class);
        $this->storeRepository = Bootstrap::getObjectManager()->get(StoreRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     *
     * @param string $sku
     * @param int $stockId
     * @param float $qty
     * @param float $reservedQty
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Validation\ValidationException
     *
     * @dataProvider productsInStockDataProvider
     */
    public function testAddInStockProductToQuote(
        string $sku,
        int $stockId,
        float $qty,
        float $reservedQty
    ) {
        $this->markTestSkipped(
            'Fix add to Cart Integration Test https://github.com/magento-engcom/msi/issues/688'
        );
        $quote = $this->getQuote($stockId);
        $this->appendReservation($sku, -$reservedQty, $stockId);
        $product = $this->getProductBySku($sku);

        $quote->addProduct($product, $qty);

        /** @var CartItemInterface $quoteItem */
        $quoteItem = current($quote->getAllItems());
        self::assertEquals($qty, $quoteItem->getQty());

        //cleanup
        $this->appendReservation($sku, $reservedQty, $stockId);
    }

    /**
     * @see ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @return array
     */
    public function productsInStockDataProvider(): array
    {
        return [
            ['SKU-1', 10, 4, 1.5],
            ['SKU-1', 20, 2, 1],
            ['SKU-2', 30, 1.5, 3.5],
            ['SKU-2', 30, 1, 1.7],
        ];
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     *
     * @param string $sku
     * @param int $stockId
     * @param float $qty
     * @param float $reservedQty
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Validation\ValidationException
     *
     * @dataProvider notSalableProductsDataProvider
     */
    public function testAddOutOffStockProductToQuote(
        string $sku,
        int $stockId,
        float $qty,
        float $reservedQty
    ) {
        $this->markTestSkipped(
            'Fix add to Cart Integration Test https://github.com/magento-engcom/msi/issues/688'
        );
        $quote = $this->getQuote($stockId);
        $this->appendReservation($sku, -$reservedQty, $stockId);
        $product = $this->getProductBySku($sku);

        self::expectException(LocalizedException::class);
        $quote->addProduct($product, $qty);

        $quoteItemCount = count($quote->getAllItems());
        self::assertEquals(0, $quoteItemCount);

        //cleanup
        $this->appendReservation($sku, $reservedQty, $stockId);
    }

    /**
     * @see ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @return array
     */
    public function notSalableProductsDataProvider(): array
    {
        return [
            ['SKU-1', 20, 2.5, 2.5],
            ['SKU-1', 30, 1.8, 4],
            ['SKU-2', 30, 1.5, 3.6],
            ['SKU-3', 20, 1.9, 1.5]
        ];
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
     * @param int $stockId
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Validation\ValidationException
     */
    private function appendReservation(string $productSku, float $qty, int $stockId)
    {
        $this->appendReservations->execute([
            $this->reservationBuilder->setStockId($stockId)->setSku($productSku)->setQuantity($qty)->build(),
        ]);
    }

    /**
     * @param int $stockId
     * @return Quote
     * @throws NoSuchEntityException
     */
    private function getQuote(int $stockId): Quote
    {
        /** @var StockInterface $stock */
        $stock = $this->stockRepository->get($stockId);
        /** @var SalesChannelInterface[] $salesChannels */
        $salesChannels = $stock->getExtensionAttributes()->getSalesChannels();
        $storeCode = 'store_for_';
        foreach ($salesChannels as $salesChannel) {
            if ($salesChannel->getType() == SalesChannelInterface::TYPE_WEBSITE) {
                $storeCode .= $salesChannel->getCode();
                break;
            }
        }
        /** @var StoreInterface $store */
        $store = $this->storeRepository->get($storeCode);

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

    protected function tearDown()
    {
        $this->cleanupReservations->execute();
    }
}
