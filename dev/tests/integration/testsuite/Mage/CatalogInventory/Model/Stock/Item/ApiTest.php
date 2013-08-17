<?php
/**
 * Stock item API tests.
 *
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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @magentoDataFixture Mage/Catalog/_files/multiple_products.php
 */
class Mage_CatalogInventory_Model_Stock_Item_ApiTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test list method.
     */
    public function testList()
    {
        $productsId = array(10, 11, 12);
        /** Retrieve products stock data. */
        $productsStockData = Magento_Test_Helper_Api::call(
            $this,
            'catalogInventoryStockItemList',
            array($productsId)
        );
        /** Assert product stock data retrieving was successful. */
        $this->assertNotEmpty($productsStockData, 'Product stock data retrieving was unsuccessful.');
        /** Assert retrieved product stock data is correct. */
        $expectedData = array(
            'product_id' => '10',
            'sku' => 'simple1',
            'qty' => 100,
            'is_in_stock' => '1'
        );
        $stockData = reset($productsStockData);
        $this->assertEquals($expectedData, $stockData, 'Product stock data is incorrect.');
    }

    /**
     * Test update method.
     */
    public function testUpdate()
    {
        $newQty = 333;

        $result = Magento_Test_Helper_Api::call(
            $this,
            'catalogInventoryStockItemUpdate',
            array(10, array('qty' => $newQty))
        );

        $this->assertTrue($result, 'Failed updating stock item.');
        /** @var $product Mage_Catalog_Model_Product */
        $product = Mage::getModel('Mage_Catalog_Model_Product')->load(10);

        $this->assertEquals(
            $newQty,
            $product->getStockItem()->getQty(),
            'Actual quantity does not match the expected one.'
        );
    }

    /**
     * Test updating multiple stock items at once.
     */
    public function testMultiUpdate()
    {
        $productIds = array(10, 11, 12);

        $productData = array(
            array('qty' => 1010),
            array('qty' => 1111),
            array('qty' => 1212),
        );

        $result = Magento_Test_Helper_Api::call(
            $this,
            'catalogInventoryStockItemMultiUpdate',
            array($productIds, $productData)
        );

        $this->assertTrue($result, 'Failed updating stock items.');
        /** @var $product Mage_Catalog_Model_Product */
        $product = Mage::getModel('Mage_Catalog_Model_Product');

        foreach ($productIds as $index => $productId) {
            $qty = $product->load($productId)->getStockItem()->getQty();
            $this->assertEquals($productData[$index]['qty'], $qty, 'Actual quantity does not match the expected one.');
        }
    }
}
