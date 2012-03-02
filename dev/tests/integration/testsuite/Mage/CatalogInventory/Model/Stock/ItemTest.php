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
 * @category    Magento
 * @package     Mage_CatalogInventory
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @group module:Mage_CatalogInventory
 */
class Mage_CatalogInventory_Model_Stock_ItemTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_CatalogInventory_Model_Stock_Item
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new Mage_CatalogInventory_Model_Stock_Item;
    }

    /**
     * Simple product with stock item
     */
    public static function simpleProductFixture()
    {
        $product = new Mage_Catalog_Model_Product();
        $product->setTypeId('simple')
            ->setId(1)
            ->setAttributeSetId(4)
            ->setName('Simple Product')
            ->setSku('simple')
            ->setPrice(10)
            ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
            ->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
            ->save();
    }

    /**
     * @magentoDataFixture simpleProductFixture
     */
    public function testStockStatusChangedAuto()
    {
        $this->_model
            ->setProductId(1)
            ->setTypeId(Mage_Catalog_Model_Product_Type::DEFAULT_TYPE)
            ->setStockId(Mage_CatalogInventory_Model_Stock::DEFAULT_STOCK_ID)
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

    public function testSetGetProduct()
    {
        $this->assertNull($this->_model->getProduct());
        $productOne = new Varien_Object;
        $this->_model->setData('product', $productOne);
        $this->assertSame($productOne, $this->_model->getProduct());

        $productTwo = new Varien_Object;
        $this->_model->setProduct($productTwo);
        $this->assertSame($productTwo, $this->_model->getProduct());
    }
}
