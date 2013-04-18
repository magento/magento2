<?php
/**
 * Product API tests.
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
 */
class Mage_Catalog_Model_Product_ApiTest extends PHPUnit_Framework_TestCase
{
    /**
     * @magentoDataFixture Mage/Catalog/_files/product_special_price.php
     */
    public function testGetSpecialPrice()
    {
        /** Retrieve the product data. */
        $productId = 1;
        $actualProductData = Magento_Test_Helper_Api::call(
            $this,
            'catalogProductGetSpecialPrice',
            array('productId' => $productId)
        );
        /** Assert returned product data. */
        $this->assertNotEmpty($actualProductData, 'Missing special price response data.');

        /** @var Mage_Catalog_Model_Product $expectedProduct */
        $expectedProduct = Mage::getModel('Mage_Catalog_Model_Product');
        $expectedProduct->load($productId);
        $fieldsToCompare = array(
            'entity_id' => 'product_id',
            'sku',
            'attribute_set_id' => 'set',
            'type_id' => 'type',
            'category_ids' => 'categories',
            'special_price'
        );
        /** Assert response product equals to actual product data. */
        Magento_Test_Helper_Api::checkEntityFields(
            $this,
            $expectedProduct->getData(),
            $actualProductData,
            $fieldsToCompare
        );
    }

    /**
     * @magentoDataFixture Mage/Catalog/_files/multiple_products.php
     */
    public function testItems()
    {
        /** Retrieve the list of products. */
        $actualProductsData = Magento_Test_Helper_Api::call(
            $this,
            'catalogProductList'
        );
        /** Assert that products quantity equals to 3. */
        $this->assertCount(3, $actualProductsData, 'Products quantity is invalid.');

        /** Loading expected product from fixture. */
        $expectedProduct = Mage::getModel('Mage_Catalog_Model_Product');
        $expectedProduct->load(10);
        $fieldsToCompare = array(
            'entity_id' => 'product_id',
            'sku',
            'attribute_set_id' => 'set',
            'type_id' => 'type',
            'category_ids',
        );
        /** Assert first product from response equals to actual product data. */
        Magento_Test_Helper_Api::checkEntityFields(
            $this,
            $expectedProduct->getData(),
            reset($actualProductsData),
            $fieldsToCompare
        );
    }

    /**
     * Test retrieving the list of attributes which are not in default create/update list via API.
     */
    public function testGetAdditionalAttributes()
    {
        $attributesList = Magento_Test_Helper_Api::call(
            $this,
            'catalogProductListOfAdditionalAttributes',
            array('simple', 4)
        );
        $this->assertGreaterThan(20, count($attributesList), "Attributes quantity seems to be incorrect.");
        $oldIdAttributeData = reset($attributesList);
        $oldIdExpectedData = array(
            'attribute_id' => '89',
            'code' => 'old_id',
            'type' => 'text',
            'required' => '0',
            'scope' => 'global'
        );
        $this->assertEquals($oldIdExpectedData, $oldIdAttributeData, "Attribute data from the list is incorrect.");
    }
}
