<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Ui\DataProvider\Product;

use Magento\Eav\Setup\EavSetup;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\CatalogInventory\Api\StockItemCriteriaInterface;
use Magento\Catalog\Api\Data\EavAttributeInterface;

/**
 * Class QuantityAndStockStatusTest
 */
class QuantityAndStockStatusTest extends TestCase
{
    /**
     * @var string
     */
    private static $quantityAndStockStatus = 'quantity_and_stock_status';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var int
     */
    private $isUsedInGridValue;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $eavSetup = $this->objectManager->create(EavSetup::class);
        $this->isUsedInGridValue = $eavSetup->getAttribute(
            Product::ENTITY,
            self::$quantityAndStockStatus,
            EavAttributeInterface::IS_USED_IN_GRID
        );
        $eavSetup->addAttribute(
            Product::ENTITY,
            self::$quantityAndStockStatus,
            [
                EavAttributeInterface::IS_USED_IN_GRID => 1,
            ]
        );
    }

    /**
     * Test product stock status in the products grid column
     *
     * @magentoDataFixture Magento/Checkout/_files/simple_product.php
     */
    public function testProductStockStatus()
    {
        $stockItemRepository = $this->objectManager->create(StockItemRepository::class);

        /** @var StockItemCriteriaInterface $stockItemCriteria */
        $stockItemCriteria = $this->objectManager->create(StockItemCriteriaInterface::class);

        $savedStockItem = current($stockItemRepository->getList($stockItemCriteria)
            ->getItems());
        $savedStockItemId = $savedStockItem->getItemId();

        $savedStockItem->setIsInStock(true);
        $savedStockItem->save();

        $savedStockItem->setIsInStock(false);
        $savedStockItem->save();

        $dataProvider = $this->objectManager->create(
            ProductDataProvider::class,
            [
                'name' => 'product_listing_data_source',
                'primaryFieldName' => 'entity_id',
                'requestFieldName' => 'id',
                'addFieldStrategies' => [
                    'quantity_and_stock_status' =>
                        $this->objectManager->get(AddQuantityAndStockStatusFieldToCollection::class)
                ]
            ]
        );

        $dataProvider->addField(self::$quantityAndStockStatus);
        $data = $dataProvider->getData();

        $this->assertEquals(
            $data['items'][0][self::$quantityAndStockStatus],
            $savedStockItem->load($savedStockItemId)
                ->getData('is_in_stock')
        );
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $eavSetup = $this->objectManager->create(EavSetup::class);
        $eavSetup->addAttribute(
            Product::ENTITY,
            self::$quantityAndStockStatus,
            [
                EavAttributeInterface::IS_USED_IN_GRID => $this->isUsedInGridValue,
            ]
        );
        parent::tearDown();
    }
}
