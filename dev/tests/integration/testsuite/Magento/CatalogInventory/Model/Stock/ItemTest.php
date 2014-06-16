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
     * Simple product with stock item
     */
    public static function simpleProductFixture()
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->setTypeId('simple')
            ->setId(1)
            ->setAttributeSetId(4)
            ->setName('Simple Product')
            ->setSku('simple')
            ->setPrice(10)
            ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
            ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
            ->save();
    }

    /**
     * @magentoDataFixture simpleProductFixture
     */
    public function testSaveWithNullQty()
    {
        $this->_model->setProductId(1)
            ->setTypeId(\Magento\Catalog\Model\Product\Type::DEFAULT_TYPE)
            ->setStockId(\Magento\CatalogInventory\Model\Stock::DEFAULT_STOCK_ID)
            ->setQty(null);
        $this->_model->save();

        $this->_model->setQty(2);
        $this->_model->save();
        $this->assertEquals('2.0000', $this->_model->load(1)->getQty());

        $this->_model->setQty(0);
        $this->_model->save();
        $this->assertEquals('0.0000', $this->_model->load(1)->getQty());

        $this->_model->setQty(null);
        $this->_model->save();
        $this->assertEquals(null, $this->_model->load(1)->getQty());
    }

    /**
     * @magentoDataFixture simpleProductFixture
     */
    public function testStockStatusChangedAuto()
    {
        $this->_model->setProductId(1)
            ->setTypeId(\Magento\Catalog\Model\Product\Type::DEFAULT_TYPE)
            ->setStockId(\Magento\CatalogInventory\Model\Stock::DEFAULT_STOCK_ID)
            ->setQty(1);
        $this->_model->save();
        $this->assertEquals(0, $this->_model->getStockStatusChangedAuto());

        $this->_model->setStockStatusChangedAutomaticallyFlag(1);
        $this->_model->save();
        $this->assertEquals(1, $this->_model->getStockStatusChangedAuto());
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
