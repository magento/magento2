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
 * @package     Mage_Adminhtml
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Adminhtml_Catalog_Product_AttributeControllerTest extends Mage_Backend_Utility_Controller
{
    /**
     * @magentoDataFixture Mage/Catalog/controllers/_files/attribute_system.php
     */
    public function testSaveActionApplyToDataSystemAttribute()
    {
        $postData = $this->_getAttributeData() + array('attribute_id' => '2');
        $this->getRequest()->setPost($postData);
        $this->dispatch('backend/admin/catalog_product_attribute/save');
        $model = new Mage_Catalog_Model_Resource_Eav_Attribute(
            Mage::getModel('Mage_Core_Model_Event_Manager'),
            Mage::getModel('Mage_Core_Model_Cache')
        );
        $model->load($postData['attribute_id']);
        $this->assertNull($model->getData('apply_to'));
    }

    /**
     * @magentoDataFixture Mage/Catalog/controllers/_files/attribute_user_defined.php
     */
    public function testSaveActionApplyToDataUserDefinedAttribute()
    {
        $postData = $this->_getAttributeData() + array('attribute_id' => '1');
        $this->getRequest()->setPost($postData);
        $this->dispatch('backend/admin/catalog_product_attribute/save');
        $model = new Mage_Catalog_Model_Resource_Eav_Attribute(
            Mage::getModel('Mage_Core_Model_Event_Manager'),
            Mage::getModel('Mage_Core_Model_Cache')
        );
        $model->load($postData['attribute_id']);
        $this->assertEquals('simple,configurable', $model->getData('apply_to'));
    }

    /**
     * @magentoDataFixture Mage/Catalog/controllers/_files/attribute_system_with_applyto_data.php
     */
    public function testSaveActionApplyToData()
    {
        $postData = $this->_getAttributeData() + array('attribute_id' => '3');
        unset($postData['apply_to']);
        $this->getRequest()->setPost($postData);
        $this->dispatch('backend/admin/catalog_product_attribute/save');
        $model = new Mage_Catalog_Model_Resource_Eav_Attribute(
            Mage::getModel('Mage_Core_Model_Event_Manager'),
            Mage::getModel('Mage_Core_Model_Cache')
        );
        $model->load($postData['attribute_id']);
        $this->assertEquals(array('simple', 'configurable'), $model->getApplyTo());
    }

    /**
     * Get attribute data for post
     *
     * @return array
     */
    protected function _getAttributeData()
    {
        return array(
            'is_global' => '2',
            'default_value_text' => '0',
            'default_value_yesno' => '0',
            'default_value_date' => '',
            'default_value_textarea' => '0',
            'is_required' => '1',
            'frontend_class' => '',
            'is_configurable' => '0',
            'is_searchable' => '0',
            'is_visible_in_advanced_search' => '0',
            'is_comparable' => '0',
            'is_filterable' => '0',
            'is_filterable_in_search' => '0',
            'is_used_for_promo_rules' => '0',
            'is_html_allowed_on_front' => '0',
            'is_visible_on_front' => '0',
            'used_in_product_listing' => '1',
            'used_for_sort_by' => '0',
            'apply_to' => array(
        'simple', 'configurable'),
            'frontend_label' => array(
        0 => 'Allow Open Amount',
        1 => ''),
            'default' => array(
        0 => '0'),
            'option' => array(
        'delete' => array(
            0 => '',
            1 => '')));
    }
}
