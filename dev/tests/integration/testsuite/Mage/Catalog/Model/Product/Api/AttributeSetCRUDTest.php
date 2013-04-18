<?php
/**
 * Product attribute set API model test.
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
class Mage_Catalog_Model_Product_Api_AttributeSetCRUDTest extends PHPUnit_Framework_TestCase
{
    /**
     * Remove attribute set
     *
     * @param int $attrSetId
     */
    protected function _removeAttrSet($attrSetId)
    {
        /** @var $attrSet Mage_Eav_Model_Entity_Attribute_Set */
        $attrSet = Mage::getModel('Mage_Eav_Model_Entity_Attribute_Set');

        $attrSet->setId($attrSetId);
        $attrSet->delete();
    }

    /**
     * Remove attributes
     *
     * @param array $attrIds
     */
    protected function _removeAttributes($attrIds)
    {
        /** @var $attr Mage_Eav_Model_Entity_Attribute */
        $attr = Mage::getModel('Mage_Eav_Model_Entity_Attribute');

        if (!is_array($attrIds)) {
            $attrIds = array($attrIds);
        }
        foreach ($attrIds as $attrId) {
            $attr->setId($attrId);
            $attr->delete();
        }
    }

    /**
     * Test Attribute set CRUD
     *
     * @magentoDbIsolation enabled
     */
    public function testAttributeSetCRUD()
    {
        $attributeSetFixture = simplexml_load_file(dirname(__FILE__) . '/_files/_data/xml/AttributeSet.xml');
        $data = Magento_Test_Helper_Api::simpleXmlToArray($attributeSetFixture->create);
        $data['attributeSetName'] = $data['attributeSetName'] . ' ' . mt_rand(1000, 9999);

        // create test
        $createdAttrSetId = Magento_Test_Helper_Api::call(
            $this,
            'catalogProductAttributeSetCreate',
            array($data['attributeSetName'], $data['skeletonSetId'])
        );
        $this->assertGreaterThan(0, $createdAttrSetId);

        // Duplicate name exception test
        try {
            Magento_Test_Helper_Api::call(
                $this,
                'catalogProductAttributeSetCreate',
                array($data['attributeSetName'], $data['skeletonSetId'])
            );
            $this->fail("Didn't receive exception!");
        } catch (Exception $e) {
        }

        // items list test
        $attrSetList = Magento_Test_Helper_Api::call($this, 'catalogProductAttributeSetList');
        $completeFlag = false;
        foreach ($attrSetList as $attrSet) {
            if ($attrSet['set_id'] == $createdAttrSetId) {
                $this->assertEquals($data['attributeSetName'], $attrSet['name']);
                $completeFlag = true;
                break;
            }
        }
        $this->assertTrue($completeFlag, "Can't find added attribute set in list");

        // Remove AttrSet with related products
        $productData = Magento_Test_Helper_Api::simpleXmlToArray($attributeSetFixture->relatedProduct);
        $productData['sku'] = $productData['sku'] . '_' . mt_rand(1000, 9999);
        $productId = Magento_Test_Helper_Api::call(
            $this,
            'catalogProductCreate',
            array(
                'type' => $productData['typeId'],
                'set' => $createdAttrSetId,
                'sku' => $productData['sku'],
                'productData' => $productData['productData']
            )
        );

        try {
            Magento_Test_Helper_Api::call(
                $this,
                'catalogProductAttributeSetRemove',
                array('attributeSetId' => $createdAttrSetId)
            );
            $this->fail("Didn't receive exception!");
        } catch (Exception $e) {
        }

        Magento_Test_Helper_Api::call($this, 'catalogProductDelete', array('productId' => $productId));

        // delete test
        $attributeSetDelete = Magento_Test_Helper_Api::call(
            $this,
            'catalogProductAttributeSetRemove',
            array('attributeSetId' => $createdAttrSetId)
        );
        $this->assertTrue((bool)$attributeSetDelete, "Can't delete added attribute set");

        // Test delete undefined attribute set and check successful delete in previous call
        try {
            Magento_Test_Helper_Api::call(
                $this,
                'catalogProductAttributeSetRemove',
                array('attributeSetId' => $createdAttrSetId)
            );
            $this->fail("Didn't receive exception!");
        } catch (Exception $e) {
        }

    }

    /**
     * Test attribute CRUD in attribute set
     *
     * @magentoDataFixture Mage/Catalog/Model/Product/Api/_files/AttributeSet.php
     */
    public function testAttributeSetAttrCRUD()
    {
        $testAttributeSetId = Mage::registry('testAttributeSetId');
        $attrIdsArray = Mage::registry('testAttributeSetAttrIdsArray');

        // add attribute test
        $addResult = Magento_Test_Helper_Api::call(
            $this,
            'catalogProductAttributeSetAttributeAdd',
            array('attributeId' => $attrIdsArray[0], 'attributeSetId' => $testAttributeSetId)
        );
        $this->assertTrue((bool)$addResult);

        // delete attribute test
        $removeResult = Magento_Test_Helper_Api::call(
            $this,
            'catalogProductAttributeSetAttributeRemove',
            array('attributeId' => $attrIdsArray[0], 'attributeSetId' => $testAttributeSetId)
        );
        $this->assertTrue((bool)$removeResult);
    }

    /**
     * Test group of attribute sets CRUD
     *
     * @magentoDataFixture Mage/Catalog/Model/Product/Api/_files/AttributeSet.php
     */
    public function testAttributeSetGroupCRUD()
    {
        $testAttributeSetId = Mage::registry('testAttributeSetId');
        $attributeSetFixture = simplexml_load_file(dirname(__FILE__) . '/_files/_data/xml/AttributeSet.xml');
        $data = Magento_Test_Helper_Api::simpleXmlToArray($attributeSetFixture->groupAdd);

        // add group test
        $attrSetGroupId = Magento_Test_Helper_Api::call(
            $this,
            'catalogProductAttributeSetGroupAdd',
            array('attributeSetId' => $testAttributeSetId, 'groupName' => $data['groupName'])
        );
        $this->assertGreaterThan(0, $attrSetGroupId);

        // add already exist group exception test
        try {
            $attrSetGroupId = Magento_Test_Helper_Api::call(
                $this,
                'catalogProductAttributeSetGroupAdd',
                array('attributeSetId' => $testAttributeSetId, 'groupName' => $data['existsGroupName'])
            );
            $this->fail("Didn't receive exception!");
        } catch (Exception $e) {
        }

        // rename group test
        $groupName = $data['groupName'] . ' ' . mt_rand(1000, 9999);
        $renameResult = Magento_Test_Helper_Api::call(
            $this,
            'catalogProductAttributeSetGroupRename',
            array('groupId' => $attrSetGroupId, 'groupName' => $groupName)
        );
        $this->assertTrue((bool)$renameResult);

        // remove group test
        $removeResult = Magento_Test_Helper_Api::call(
            $this,
            'catalogProductAttributeSetGroupRemove',
            array('attributeGroupId' => $attrSetGroupId)
        );
        $this->assertTrue((bool)$removeResult);

        $this->_removeAttrSet($testAttributeSetId);
        $this->_removeAttributes(Mage::registry('testAttributeSetAttrIdsArray'));

        // remove undefined group exception test
        $this->setExpectedException('SoapFault');
        Magento_Test_Helper_Api::call(
            $this,
            'catalogProductAttributeSetGroupRemove',
            array('attributeGroupId' => $attrSetGroupId)
        );
    }
}
