<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\CatalogInventory\Model\Stock;

class ItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogInventory\Model\Stock\Item
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\CatalogInventory\Model\Stock\Item'
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products.php
     */
    public function testSaveWithNullQty()
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Catalog\Model\Product');

        $product->load(1);

        /** @var \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository */
        $stockItemRepository = $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\CatalogInventory\Model\Stock\StockItemRepository');

        /** @var \Magento\CatalogInventory\Api\StockItemCriteriaInterface $stockItemCriteria */
        $stockItemCriteria = $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\CatalogInventory\Api\StockItemCriteriaInterface');

        $savedStockItem = current($stockItemRepository->getList($stockItemCriteria)->getItems());
        $savedStockItemId = $savedStockItem->getItemId();

        $savedStockItem->setQty(null);
        $savedStockItem->save();

        $savedStockItem->setQty(2);
        $savedStockItem->save();
        $this->assertEquals('2.0000', $savedStockItem->load($savedStockItemId)->getQty());

        $savedStockItem->setQty(0);
        $savedStockItem->save();
        $this->assertEquals('0.0000', $savedStockItem->load($savedStockItemId)->getQty());

        $savedStockItem->setQty(null);
        $savedStockItem->save();

        $this->assertEquals(null, $savedStockItem->load($savedStockItemId)->getQty());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products.php
     */
    public function testStockStatusChangedAuto()
    {
        /** @var \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository */
        $stockItemRepository = $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\CatalogInventory\Model\Stock\StockItemRepository');

        /** @var \Magento\CatalogInventory\Api\StockItemCriteriaInterface $stockItemCriteria */
        $stockItemCriteria = $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\CatalogInventory\Api\StockItemCriteriaInterface');

        $savedStockItem = current($stockItemRepository->getList($stockItemCriteria)->getItems());

        $savedStockItem->setQty(1);
        $savedStockItem->save();

        $this->assertEquals(0, $savedStockItem->getStockStatusChangedAuto());

        $savedStockItem->setStockStatusChangedAutomaticallyFlag(1);
        $savedStockItem->save();
        $this->assertEquals(1, $savedStockItem->getStockStatusChangedAuto());
    }

    /**
     * @magentoConfigFixture current_store cataloginventory/item_options/enable_qty_increments 1
     */
    public function testSetGetEnableQtyIncrements()
    {
        $this->assertFalse($this->_model->getEnableQtyIncrements());

        $this->_model->setUseConfigEnableQtyInc(true);
        $this->assertTrue($this->_model->getEnableQtyIncrements());
    }
}
