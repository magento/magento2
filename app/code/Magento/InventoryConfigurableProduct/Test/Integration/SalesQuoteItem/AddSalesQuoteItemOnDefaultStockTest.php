<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProduct\Test\Integration\SalesQuoteItem;

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
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/default_stock_configurable_products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     */
    public function testAddOutOfStockProductToQuote()
    {
        $productSku = 'configurable_out_of_stock';
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
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/default_stock_configurable_products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     */
    public function testAddInStockProductToQuote()
    {
        $productSku = 'configurable_in_stock';
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
     */
    private function getBuyRequest(ProductInterface $product, float $productQty): DataObject
    {
        $configurableOptions = $product->getTypeInstance()->getConfigurableOptions($product);
        $option = current(current($configurableOptions));

        return $this->dataObjectFactory->create(
            [
                'product' => $option['product_id'],
                'super_attribute' => [key($configurableOptions) => $option['value_index']],
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
