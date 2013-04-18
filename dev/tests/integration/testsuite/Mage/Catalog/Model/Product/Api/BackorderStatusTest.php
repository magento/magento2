<?php
/**
 * Test updating product back-order status through API
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
 * @magentoDbIsolation enabled
 */
class Mage_Catalog_Model_Product_Api_BackorderStatusTest extends PHPUnit_Framework_TestCase
{
    /** @var Mage_Catalog_Model_Product */
    protected $_product;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $productData = require dirname(__FILE__) . '/_files/ProductData.php';
        $product = Mage::getModel('Mage_Catalog_Model_Product');

        $product->setData($productData['create_full_fledged']);
        $product->save();

        $this->_product = $product;

        parent::setUp();
    }

    /**
     * Test updating product back-order status
     */
    public function testBackorderStatusUpdate()
    {
        $newProductData = array(
            'use_config_manage_stock' => 0,
            'manage_stock' => 1,
            'is_in_stock' => 0,
            'use_config_backorders' => 0,
            'backorders' => 1,
        );

        $result = Magento_Test_Helper_Api::call(
            $this,
            'catalogInventoryStockItemUpdate',
            array(
                'productId' => $this->_product->getSku(),
                'data' => $newProductData
            )
        );

        $this->assertEquals(1, $result);
        // have to re-load product for stock item set
        $this->_product->load($this->_product->getId());
        $this->assertEquals(1, $this->_product->getStockItem()->getBackorders());
        $this->assertEquals(0, $this->_product->getStockItem()->getUseConfigBackorders());
    }
}
