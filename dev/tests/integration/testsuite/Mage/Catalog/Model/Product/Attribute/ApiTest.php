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
 * @package     Magento_Catalog
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Catalog_Model_Product_Attribute_Api.
 */
class Mage_Catalog_Model_Product_Attribute_ApiTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Catalog_Model_Product_Attribute_Api
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = Mage::getModel('Mage_Catalog_Model_Product_Attribute_Api');
    }

    protected function tearDown()
    {
        $this->_model = null;
    }

    public function testItems()
    {
        $items = $this->_model->items(4); /* default product attribute set after installation */
        $this->assertInternalType('array', $items);
        $element = current($items);
        $this->assertArrayHasKey('attribute_id', $element);
        $this->assertArrayHasKey('code', $element);
        $this->assertArrayHasKey('type', $element);
        $this->assertArrayHasKey('required', $element);
        $this->assertArrayHasKey('scope', $element);
        foreach ($items as $item) {
            if ($item['code'] == 'status') {
                return $item['attribute_id'];
            }
        }
        return false;
    }

    /**
     * @depends testItems
     */
    public function testOptions($attributeId)
    {
        if (!$attributeId) {
            $this->fail('Invalid attribute ID.');
        }
        $options = $this->_model->options($attributeId);
        $this->assertInternalType('array', $options);
        $element = current($options);
        $this->assertArrayHasKey('value', $element);
        $this->assertArrayHasKey('label', $element);
    }

    /**
     * Test types method
     */
    public function testTypes()
    {
        $expectedTypes = Mage::getModel('Mage_Catalog_Model_Product_Attribute_Source_Inputtype')->toOptionArray();
        $types = Magento_Test_Helper_Api::call($this, 'catalogProductAttributeTypes');
        $this->assertEquals($expectedTypes, $types);
    }

    /**
     * Test attribute creation.
     *
     * @magentoDbIsolation enabled
     */
    public function testCreate()
    {
        $attributeCode = "test_attribute";
        $dataToCreate = array(
            "attribute_code" => $attributeCode,
            "frontend_input" => "text",
            "scope" => "store",
            "default_value" => "1",
            "is_unique" => 0,
            "is_required" => 0,
            "apply_to" => array("simple"),
            "is_configurable" => 0,
            "is_searchable" => 0,
            "is_visible_in_advanced_search" => 0,
            "is_comparable" => 0,
            "is_used_for_promo_rules" => 0,
            "is_visible_on_front" => 0,
            "used_in_product_listing" => 0,
            "frontend_label" => array(
                array(
                    "store_id" => "0",
                    "label" => "some label",
                )
            )
        );
        $attributeId = Magento_Test_Helper_Api::call(
            $this,
            'catalogProductAttributeCreate',
            array(
                'data' => (object)$dataToCreate
            )
        );
        $this->assertGreaterThan(0, $attributeId, 'Attribute create was not successful.');

        $this->_verifyAttribute($attributeCode, $dataToCreate);
    }

    /**
     * Test attribute update.
     *
     * @magentoDataFixture Mage/Catalog/Model/Product/Attribute/_files/select_attribute.php
     */
    public function testUpdate()
    {
        $attributeCode = 'select_attribute';
        $dataToUpdate = array(
            "scope" => "global",
            "default_value" => "2",
            "is_unique" => 1,
            "is_required" => 1,
            "apply_to" => array("simple", "configurable"),
            "is_configurable" => 1,
            "is_searchable" => 1,
            "is_visible_in_advanced_search" => 1,
            "is_comparable" => 1,
            "is_visible_on_front" => 1,
            "used_in_product_listing" => 1,
            "frontend_label" => array(
                array(
                    "store_id" => "0",
                    "label" => "Label Updated"
                )
            )
        );

        $result = Magento_Test_Helper_Api::call(
            $this,
            'catalogProductAttributeUpdate',
            array(
                'attribute' => $attributeCode,
                'data' => (object)$dataToUpdate,
            )
        );
        $this->assertTrue($result, 'Attribute update was not successful.');

        $this->_verifyAttribute($attributeCode, $dataToUpdate);
    }

    /**
     * Test attribute info.
     *
     * @magentoDataFixture Mage/Catalog/Model/Product/Attribute/_files/select_attribute.php
     */
    public function testInfo()
    {
        $attributeCode = 'select_attribute';
        $attributeInfo = Magento_Test_Helper_Api::call(
            $this,
            'catalogProductAttributeInfo',
            array(
                'attribute' => $attributeCode,
            )
        );
        $this->assertNotEmpty($attributeInfo, 'Attribute info was not retrieved.');

        $fieldsToCompare = array(
            'attribute_id',
            'attribute_code',
            'frontend_input',
            'is_unique',
            'is_required',
            'is_configurable',
            'is_searchable',
            'is_visible_in_advanced_search',
            'is_comparable',
            'is_used_for_promo_rules',
            'is_visible_on_front',
            'used_in_product_listing',
        );
        $this->_verifyAttribute($attributeCode, $attributeInfo, $fieldsToCompare);
    }

    /**
     * Verify given attribute data.
     *
     * @param string $attributeCode
     * @param array $actualData
     * @param array $fieldsToCompare
     */
    protected function _verifyAttribute($attributeCode, array $actualData, array $fieldsToCompare = array())
    {
        /** @var Mage_Catalog_Model_Resource_Eav_Attribute $expectedAttribute */
        $expectedAttribute = Mage::getResourceModel('Mage_Catalog_Model_Resource_Eav_Attribute');
        $expectedAttribute->loadByCode('catalog_product', $attributeCode);
        $expectedIsGlobal = $actualData['scope'] == 'global' ? 1 : 0;
        $this->assertEquals($expectedIsGlobal, $expectedAttribute->getIsGlobal(), 'Attribute scope is incorrect.');
        $this->assertEquals(
            $expectedAttribute->getApplyTo(),
            $actualData['apply_to'],
            'Attribute "Apply To" is incorrect.'
        );

        $frontendLabels = $actualData['frontend_label'];
        $frontendLabel = reset($frontendLabels);
        $this->assertEquals(
            $expectedAttribute->getFrontendLabel(),
            $frontendLabel['label'],
            'Attribute fronted label is incorrect.'
        );
        unset($actualData['scope']);
        unset($actualData['apply_to']);
        unset($actualData['frontend_label']);
        if (empty($fieldsToCompare)) {
            $fieldsToCompare = array_keys($actualData);
        }

        Magento_Test_Helper_Api::checkEntityFields(
            $this,
            $expectedAttribute->getData(),
            $actualData,
            $fieldsToCompare
        );
    }

    /**
     * Test attribute removal.
     *
     * @magentoDataFixture Mage/Catalog/Model/Product/Attribute/_files/select_attribute.php
     */
    public function testRemove()
    {
        $attributeCode = 'select_attribute';
        $result = Magento_Test_Helper_Api::call(
            $this,
            'catalogProductAttributeRemove',
            array(
                'attribute' => $attributeCode,
            )
        );
        $this->assertTrue($result, 'Attribute was not removed.');

        // Verify that attribute was deleted
        /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
        $attribute = Mage::getResourceModel('Mage_Catalog_Model_Resource_Eav_Attribute');
        $attribute->loadByCode('catalog_product', $attributeCode);
        $this->assertNull($attribute->getId(), 'Attribute was not deleted from storage.');
    }

    /**
     * Test adding an option to an attribute.
     *
     * @magentoDataFixture Mage/Catalog/Model/Product/Attribute/_files/select_attribute.php
     */
    public function testAddOption()
    {
        $attributeCode = 'select_attribute';
        $optionLabel = "Option Label";

        $data = (object)array(
            "label" => array(
                (object)array(
                    "store_id" => array("0"),
                    "value" => $optionLabel
                )
            ),
            "order" => "10",
            "is_default" => "1"
        );

        $result = Magento_Test_Helper_Api::call(
            $this,
            'catalogProductAttributeAddOption',
            array(
                'attribute' => $attributeCode,
                'data' => $data,
            )
        );
        $this->assertTrue($result, 'Attribute option was not added.');
        /** @var Mage_Catalog_Model_Resource_Eav_Attribute $expectedAttribute */
        $expectedAttribute = Mage::getModel('Mage_Catalog_Model_Resource_Eav_Attribute');
        $expectedAttribute->loadByCode('catalog_product', $attributeCode);
        $options = $expectedAttribute->getSource()->getAllOptions();
        $this->assertCount(3, $options, 'Exactly 3 options should be in attribute.');
        $option = end($options);
        $this->assertEquals($optionLabel, $option['label'], 'Incorrect option label saved.');
    }

    /**
     * Test removing an option from an attribute.
     *
     * @magentoDataFixture Mage/Catalog/Model/Product/Attribute/_files/select_attribute.php
     */
    public function testRemoveOption()
    {
        $attributeCode = 'select_attribute';
        /** @var Mage_Catalog_Model_Resource_Eav_Attribute $attribute */
        $attribute = Mage::getModel('Mage_Catalog_Model_Resource_Eav_Attribute');
        $attribute->loadByCode('catalog_product', $attributeCode);
        $options = $attribute->getSource()->getAllOptions();
        $this->assertCount(2, $options, 'Incorrect options count in fixture.');
        $optionToDelete = end($options);
        $result = Magento_Test_Helper_Api::call(
            $this,
            'catalogProductAttributeRemoveOption',
            array(
                'attribute' => $attributeCode,
                'optionId' => $optionToDelete['value'],
            )
        );
        $this->assertTrue($result, 'Attribute option was not removed.');
        // Verify option was removed from storage.
        /** @var Mage_Catalog_Model_Resource_Eav_Attribute $attrAfterRemove */
        $attrAfterRemove = Mage::getModel('Mage_Catalog_Model_Resource_Eav_Attribute');
        $attrAfterRemove->loadByCode('catalog_product', $attributeCode);
        $optionsAfterRemove = $attrAfterRemove->getSource()->getAllOptions();
        $this->assertCount(1, $optionsAfterRemove, 'Attribute option was not removed from storage.');
    }
}
