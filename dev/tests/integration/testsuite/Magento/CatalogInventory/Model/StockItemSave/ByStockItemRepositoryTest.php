<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\StockItemSave;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\TestFramework\Helper\Bootstrap;

class ByStockItemRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StockItemRepositoryInterface
     */
    private $stockItemRepository;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var StockItemDataChecker
     */
    private $stockItemDataChecker;

    /**
     * @var array
     */
    private $stockItemData = [
        StockItemInterface::QTY => 555,
        StockItemInterface::MANAGE_STOCK => true,
        StockItemInterface::IS_IN_STOCK => true,
    ];

    public function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $this->stockItemRepository = $objectManager->get(StockItemRepositoryInterface::class);
        $this->dataObjectHelper = $objectManager->get(DataObjectHelper::class);
        $this->stockItemDataChecker = $objectManager->get(StockItemDataChecker::class);
    }

    /**
     * Test stock item saving via stock item repository
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testSave()
    {
        /** @var ProductInterface $product */
        $product = $this->productRepository->get('simple', false, null, true);
        $stockItem = $product->getExtensionAttributes()->getStockItem();
        $this->dataObjectHelper->populateWithArray(
            $stockItem,
            $this->stockItemData,
            StockItemInterface::class
        );
        $this->stockItemRepository->save($stockItem);

        $this->stockItemDataChecker->checkStockItemData('simple', $this->stockItemData);
    }
}
