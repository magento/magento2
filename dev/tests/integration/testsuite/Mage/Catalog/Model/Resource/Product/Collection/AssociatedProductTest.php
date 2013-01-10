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
 * @package     Mage_Catalog
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Catalog_Model_Resource_Product_Collection_AssociatedProductTest extends PHPUnit_Framework_TestCase
{
    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Mage/Catalog/_files/product_associated.php
     */
    public function testPrepareSelect()
    {
        $product = Mage::getModel('Mage_Catalog_Model_Product');
        $product->load(1); // fixture
        $product->setId(10);
        Mage::register('current_product', $product);
        $collection = Mage::getResourceModel('Mage_Catalog_Model_Resource_Product_Collection_AssociatedProduct');
        $collectionProduct = $collection->getFirstItem();
        $this->assertEquals($product->getName(), $collectionProduct->getName());
        $this->assertEquals($product->getSku(), $collectionProduct->getSku());
        $this->assertEquals($product->getPrice(), $collectionProduct->getPrice());
        $this->assertEquals($product->getWeight(), $collectionProduct->getWeight());
        $this->assertEquals($product->getTypeId(), $collectionProduct->getTypeId());
        $this->assertEquals($product->getAttributeSetId(), $collectionProduct->getAttributeSetId());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Mage/Catalog/_files/product_associated.php
     */
    public function testPrepareSelectForSameProduct()
    {
        $product = Mage::getModel('Mage_Catalog_Model_Product');
        $product->load(1); // fixture
        Mage::register('current_product', $product);
        $collection = Mage::getResourceModel('Mage_Catalog_Model_Resource_Product_Collection_AssociatedProduct');
        $this->assertEmpty($collection->count());
    }
}
