<?php
/**
 * Catalog product link API test.
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
class Mage_Catalog_Model_Product_Link_ApiTest extends PHPUnit_Framework_TestCase
{
    const LINK_TYPE_UP_SELL = 'up_sell';
    const LINK_TYPE_RELATED = 'related';

    protected $_mainProductId = 10;
    protected $_upSellProductId = 11;
    protected $_relatedProductId = 12;

    /**
     * Test 'types' method of catalog product link API.
     */
    public function testTypes()
    {
        /** Add up-sell and related products. */
        $types = Magento_Test_Helper_Api::call($this, 'catalogProductLinkTypes');
        $expectedTypes = array('related', 'up_sell', 'cross_sell', 'grouped');
        $this->assertEquals($expectedTypes, $types);
    }

    /**
     * Test 'attributes' method of catalog product link API.
     */
    public function testAttributes()
    {
        $attributes = Magento_Test_Helper_Api::call(
            $this,
            'catalogProductLinkAttributes',
            array(self::LINK_TYPE_UP_SELL)
        );
        $this->assertEquals(
            array(array('code' => 'position', 'type' => 'int')),
            $attributes,
            "Attributes list is invalid."
        );
    }

    /**
     * Test 'assign' method of catalog product link API.
     */
    public function testAssign()
    {
        /** Add up-sell and related products. */
        $upSellResult = Magento_Test_Helper_Api::call(
            $this,
            'catalogProductLinkAssign',
            array(
                self::LINK_TYPE_UP_SELL,
                $this->_mainProductId,
                $this->_upSellProductId
            )
        );
        $this->assertTrue($upSellResult, "Up-sell link creation was unsuccessful.");
        $relatedResult = Magento_Test_Helper_Api::call(
            $this,
            'catalogProductLinkAssign',
            array(
                self::LINK_TYPE_RELATED,
                $this->_mainProductId,
                $this->_relatedProductId
            )
        );
        $this->assertTrue($relatedResult, "Related link creation was unsuccessful.");
        /** @var Mage_Catalog_Model_Product $product */
        $product = Mage::getModel('Mage_Catalog_Model_Product');
        $product->load($this->_mainProductId);

        /** Check created 'related' product link */
        $actualRelated = $product->getRelatedLinkCollection()->getItems();
        $this->assertCount(1, $actualRelated, "One link of 'related' type must exist.");
        /** @var Mage_Catalog_Model_Product_Link $relatedProductLink */
        $relatedProductLink = reset($actualRelated);
        $this->assertEquals(
            $this->_relatedProductId,
            $relatedProductLink->getLinkedProductId(),
            'Related product ID is invalid'
        );

        /** Check created 'up-sell' product link */
        $actualUpSell = $product->getUpSellLinkCollection()->getItems();
        $this->assertCount(1, $actualUpSell, "One link of 'up-sell' type must exist.");
        /** @var Mage_Catalog_Model_Product_Link $upSellProductLink */
        $upSellProductLink = reset($actualUpSell);
        $this->assertEquals(
            $this->_upSellProductId,
            $upSellProductLink->getLinkedProductId(),
            'Up-sell product ID is invalid'
        );
    }

    /**
     * Test 'items' method of catalog product API.
     *
     * @depends testAssign
     */
    public function testList()
    {
        $upSellProducts = Magento_Test_Helper_Api::call(
            $this,
            'catalogProductLinkList',
            array(
                self::LINK_TYPE_UP_SELL,
                $this->_mainProductId
            )
        );
        $this->assertCount(1, $upSellProducts, "One link of 'up-sell' type must exist.");
        $expectedFields = array('product_id', 'type', 'set', 'sku', 'position');
        $productData = reset($upSellProducts);
        $missingFields = array_diff($expectedFields, array_keys($productData));
        $this->assertEmpty(
            $missingFields,
            sprintf("The following fields must be present in response: %s.", implode(', ', $missingFields))
        );
        $this->assertEquals($this->_upSellProductId, $productData['product_id'], "Up-sell product ID is invalid.");
    }

    /**
     * Test 'update' method of catalog product API.
     *
     * @depends testList
     */
    public function testUpdate()
    {
        $positionForUpdate = 5;
        $isUpdated = Magento_Test_Helper_Api::call(
            $this,
            'catalogProductLinkUpdate',
            array(
                self::LINK_TYPE_RELATED,
                $this->_mainProductId,
                $this->_relatedProductId,
                (object)array('position' => $positionForUpdate)
            )
        );
        $this->assertTrue($isUpdated, "Related link update was unsuccessful.");

        /** Check created 'related' product link */
        /** @var Mage_Catalog_Model_Product $product */
        $product = Mage::getModel('Mage_Catalog_Model_Product');
        $product->load($this->_mainProductId);
        $actualRelated = $product->getRelatedLinkCollection()->getItems();
        $this->assertCount(1, $actualRelated, "One link of 'related' type must exist.");
        /** @var Mage_Catalog_Model_Product_Link $relatedProductLink */
        $relatedProductLink = reset($actualRelated);
        $this->assertEquals(
            $positionForUpdate,
            $relatedProductLink->getPosition(),
            'Product link position was not updated.'
        );
    }

    /**
     * Test for 'remove' method of catalog product API
     *
     * @depends testUpdate
     */
    public function testRemove()
    {
        $isRemoved = Magento_Test_Helper_Api::call(
            $this,
            'catalogProductLinkRemove',
            array(
                self::LINK_TYPE_UP_SELL,
                $this->_mainProductId,
                $this->_upSellProductId
            )
        );
        $this->assertTrue($isRemoved, "Related link remove was unsuccessful.");

        /** @var Mage_Catalog_Model_Product $product */
        $product = Mage::getModel('Mage_Catalog_Model_Product');
        $product->load($this->_mainProductId);

        /** Check that 'related' product link was not removed */
        $actualRelated = $product->getRelatedLinkCollection()->getItems();
        $this->assertCount(1, $actualRelated, "One link of 'related' type must exist.");

        /** Check that 'up-sell' product link was actually removed from DB */
        $actualUpSell = $product->getUpSellLinkCollection()->getItems();
        $this->assertCount(0, $actualUpSell, "No links of 'up-sell' type must exist.");
    }
}
