<?php
/**
 * Product tag API model test.
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
 * @magentoDataFixture Mage/Catalog/Model/Product/Api/_files/TagCRUD.php
 * @magentoDbIsolation enabled
 */
class Mage_Catalog_Model_Product_Api_TagCRUDTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test tag CRUD
     */
    public function testTagCRUD()
    {
        $tagFixture = simplexml_load_file(dirname(__FILE__) . '/_files/_data/xml/TagCRUD.xml');
        $data = Magento_Test_Helper_Api::simpleXmlToArray($tagFixture->tagData);
        $expected = Magento_Test_Helper_Api::simpleXmlToArray($tagFixture->expected);

        $data['product_id'] = Mage::registry('productData')->getId();
        $data['customer_id'] = Mage::registry('customerData')->getId();

        // create test
        $createdTags = Magento_Test_Helper_Api::call($this, 'catalogProductTagAdd', array('data' => $data));

        $this->assertCount(3, $createdTags);

        // Invalid product ID exception test
        try {
            $data['product_id'] = mt_rand(10000, 99999);
            Magento_Test_Helper_Api::call($this, 'catalogProductTagAdd', array('data' => $data));
            $this->fail("Didn't receive exception!");
        } catch (Exception $e) {
            $this->assertEquals('Requested product does not exist.', $e->getMessage());
        }

        // Invalid customer ID exception test
        try {
            $data['product_id'] = Mage::registry('productData')->getId();
            $data['customer_id'] = mt_rand(10000, 99999);
            Magento_Test_Helper_Api::call($this, 'catalogProductTagAdd', array('data' => $data));
            $this->fail("Didn't receive exception!");
        } catch (Exception $e) {
            $this->assertEquals('Requested customer does not exist.', $e->getMessage());
        }

        // Invalid store ID exception test
        try {
            $data['product_id'] = Mage::registry('productData')->getId();
            $data['customer_id'] = Mage::registry('customerData')->getId();
            $data['store'] = mt_rand(10000, 99999);
            Magento_Test_Helper_Api::call($this, 'catalogProductTagAdd', array('data' => $data));
            $this->fail("Didn't receive exception!");
        } catch (Exception $e) {
            $this->assertEquals('Requested store does not exist.', $e->getMessage());
        }

        // items list test
        $tagsList = Magento_Test_Helper_Api::call(
            $this,
            'catalogProductTagList',
            array(
                'productId' => Mage::registry('productData')->getId(),
                'store' => 0
            )
        );
        $this->assertInternalType('array', $tagsList);
        $this->assertNotEmpty($tagsList, "Can't find added tag in list");
        $this->assertCount((int)$expected['created_tags_count'], $tagsList, "Can't find added tag in list");

        // delete test
        $tagToDelete = (array)array_shift($tagsList);
        $tagDelete = Magento_Test_Helper_Api::call(
            $this,
            'catalogProductTagRemove',
            array('tagId' => $tagToDelete['tag_id'])
        );
        $this->assertTrue((bool)$tagDelete, "Can't delete added tag");

        // Delete exception test
        $this->setExpectedException('SoapFault', 'Requested tag does not exist.');
        Magento_Test_Helper_Api::call($this, 'catalogProductTagRemove', array('tagId' => $tagToDelete['tag_id']));
    }
}
