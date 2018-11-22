<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * As far none class is present as separate bundle product,
 * this test is clone of \Magento\Catalog\Model\Product with product type "bundle"
 */
namespace Magento\Bundle\Model;

use Magento\Bundle\Model\Product\Price;
use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Entity;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Product
     */
    private $model;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->model = $this->objectManager->create(Product::class);
        $this->model->setTypeId(Type::TYPE_BUNDLE);
    }

    public function testGetSetTypeInstance()
    {
        // model getter
        $typeInstance = $this->model->getTypeInstance();
        $this->assertInstanceOf(BundleType::class, $typeInstance);
        $this->assertSame($typeInstance, $this->model->getTypeInstance());

        // singleton getter
        $otherProduct = $this->objectManager->create(Product::class);
        $otherProduct->setTypeId(Type::TYPE_BUNDLE);
        $this->assertSame($typeInstance, $otherProduct->getTypeInstance());

        // model setter
        $customTypeInstance = $this->objectManager->create(BundleType::class);
        $this->model->setTypeInstance($customTypeInstance);
        $this->assertSame($customTypeInstance, $this->model->getTypeInstance());
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoAppArea adminhtml
     */
    public function testCRUD()
    {
        $this->model->setTypeId(Type::TYPE_BUNDLE)
            ->setAttributeSetId(4)
            ->setName('Bundle Product')
            ->setSku(uniqid())
            ->setPrice(10)
            ->setMetaTitle('meta title')
            ->setMetaKeyword('meta keyword')
            ->setMetaDescription('meta description')
            ->setVisibility(Visibility::VISIBILITY_BOTH)
            ->setStatus(Status::STATUS_ENABLED);
        $crud = new Entity($this->model, ['sku' => uniqid()]);
        $crud->testCrud();
    }

    public function testGetPriceModel()
    {
        $this->model->setTypeId(Type::TYPE_BUNDLE);
        $type = $this->model->getPriceModel();
        $this->assertInstanceOf(Price::class, $type);
        $this->assertSame($type, $this->model->getPriceModel());
    }

    public function testIsComposite()
    {
        $this->assertTrue($this->model->isComposite());
    }

    /**
     * Checks a case when bundle product is should be available per multiple stores.
     *
     * @magentoDataFixture Magento/Bundle/_files/product_with_multiple_options.php
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     * @magentoDbIsolation disabled
     */
    public function testMultipleStores()
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $bundle = $productRepository->get('bundle-product');

        /** @var StoreRepositoryInterface $storeRepository */
        $storeRepository = $this->objectManager->get(StoreRepositoryInterface::class);
        $store = $storeRepository->get('fixture_second_store');

        self::assertNotEquals($store->getId(), $bundle->getStoreId());

        /** @var StoreManagerInterface $storeManager */
        $storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $storeManager->setCurrentStore($store->getId());

        $bundle->setStoreId($store->getId())
            ->setCopyFromView(true);
        $updatedBundle = $productRepository->save($bundle);

        self::assertEquals($store->getId(), $updatedBundle->getStoreId());
    }

    /**
     * @param float $selectionQty
     * @param float $qty
     * @param int $isInStock
     * @param bool $manageStock
     * @param int $backorders
     * @param bool $isSalable
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Bundle/_files/product.php
     * @magentoDbIsolation enabled
     * @dataProvider stockConfigDataProvider
     * @covers \Magento\Catalog\Model\Product::isSalable
     */
    public function testIsSalable(
        float $selectionQty,
        float $qty,
        int $isInStock,
        bool $manageStock,
        int $backorders,
        bool $isSalable
    ) {
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);

        $child = $productRepository->get('simple');
        $childStockItem = $child->getExtensionAttributes()->getStockItem();
        $childStockItem->setQty($qty);
        $childStockItem->setIsInStock($isInStock);
        $childStockItem->setUseConfigManageStock(false);
        $childStockItem->setManageStock($manageStock);
        $childStockItem->setUseConfigBackorders(false);
        $childStockItem->setBackorders($backorders);
        $productRepository->save($child);

        /** @var \Magento\Catalog\Model\Product $bundle */
        $bundle = $productRepository->get('bundle-product');
        foreach ($bundle->getExtensionAttributes()->getBundleProductOptions() as $productOption) {
            foreach ($productOption->getProductLinks() as $productLink) {
                $productLink->setCanChangeQuantity(0);
                $productLink->setQty($selectionQty);
            }
        }
        $productRepository->save($bundle);

        $this->assertEquals($isSalable, $bundle->isSalable());
    }

    /**
     * @return array
     */
    public function stockConfigDataProvider(): array
    {
        $qtyVars = [0, 10];
        $isInStockVars = [
            Stock::STOCK_OUT_OF_STOCK,
            Stock::STOCK_IN_STOCK,
        ];
        $manageStockVars = [false, true];
        $backordersVars = [
            Stock::BACKORDERS_NO,
            Stock::BACKORDERS_YES_NONOTIFY,
            Stock::BACKORDERS_YES_NOTIFY,
        ];
        $selectionQtyVars = [5, 10, 15];

        $variations = [];
        foreach ($qtyVars as $qty) {
            foreach ($isInStockVars as $isInStock) {
                foreach ($manageStockVars as $manageStock) {
                    foreach ($backordersVars as $backorders) {
                        foreach ($selectionQtyVars as $selectionQty) {
                            $variationName = "selectionQty: {$selectionQty}"
                                . " qty: {$qty}"
                                . " isInStock: {$isInStock}"
                                . " manageStock: {$manageStock}"
                                . " backorders: {$backorders}";
                            $isSalable = $this->checkIsSalable(
                                $selectionQty,
                                $qty,
                                $isInStock,
                                $manageStock,
                                $backorders
                            );

                            $variations[$variationName] = [
                                $selectionQty,
                                $qty,
                                $isInStock,
                                $manageStock,
                                $backorders,
                                $isSalable
                            ];
                        }
                    }
                }
            }
        }

        return $variations;
    }

    /**
     * @param float $selectionQty
     * @param float $qty
     * @param int $isInStock
     * @param bool $manageStock
     * @param int $backorders
     * @return bool
     * @see \Magento\Bundle\Model\ResourceModel\Selection\Collection::addQuantityFilter
     */
    private function checkIsSalable(
        float $selectionQty,
        float $qty,
        int $isInStock,
        bool $manageStock,
        int $backorders
    ): bool {
        return !$manageStock || ($isInStock && ($backorders || $selectionQty <= $qty));
    }
}
