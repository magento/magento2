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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Controller\Adminhtml\Product;

/**
 * @magentoAppArea adminhtml
 */
class AttributeTest extends \Magento\Backend\Utility\Controller
{
    /**
     * @magentoDataFixture Magento/Catalog/controllers/_files/attribute_system.php
     */
    public function testSaveActionApplyToDataSystemAttribute()
    {
        $postData = $this->_getAttributeData() + array('attribute_id' => '2');
        $this->getRequest()->setPost($postData);
        $this->dispatch('backend/catalog/product_attribute/save');
        $model = $this->_objectManager->create('Magento\Catalog\Model\Resource\Eav\Attribute');
        $model->load($postData['attribute_id']);
        $this->assertNull($model->getData('apply_to'));
    }

    /**
     * @magentoDataFixture Magento/Catalog/controllers/_files/attribute_user_defined.php
     */
    public function testSaveActionApplyToDataUserDefinedAttribute()
    {
        $postData = $this->_getAttributeData() + array('attribute_id' => '1');
        $this->getRequest()->setPost($postData);
        $this->dispatch('backend/catalog/product_attribute/save');
        /** @var \Magento\Catalog\Model\Resource\Eav\Attribute $model */
        $model = $this->_objectManager->create('Magento\Catalog\Model\Resource\Eav\Attribute');
        $model->load($postData['attribute_id']);
        $this->assertEquals('simple', $model->getData('apply_to'));
    }

    /**
     * @magentoDataFixture Magento/Catalog/controllers/_files/attribute_system_with_applyto_data.php
     */
    public function testSaveActionApplyToData()
    {
        $postData = $this->_getAttributeData() + array('attribute_id' => '3');
        unset($postData['apply_to']);
        $this->getRequest()->setPost($postData);
        $this->dispatch('backend/catalog/product_attribute/save');
        $model = $this->_objectManager->create('Magento\Catalog\Model\Resource\Eav\Attribute');
        $model->load($postData['attribute_id']);
        $this->assertEquals(array('simple'), $model->getApplyTo());
    }

    /**
     * @magentoDataFixture Magento/Translation/_files/db_translate_admin_store.php
     * @magentoDataFixture Magento/Backend/controllers/_files/cache/all_types_enabled.php
     * @magentoDataFixture Magento/Catalog/controllers/_files/attribute_user_defined.php
     * @magentoAppIsolation enabled
     */
    public function testSaveActionCleanAttributeLabelCache()
    {
        /** @var \Magento\Translation\Model\Resource\String $string */
        $string = $this->_objectManager->create('Magento\Translation\Model\Resource\String');
        $this->assertEquals('predefined string translation', $this->_translate('string to translate'));
        $string->saveTranslate('string to translate', 'new string translation');
        $postData = $this->_getAttributeData() + array('attribute_id' => 1);
        $this->getRequest()->setPost($postData);
        $this->dispatch('backend/catalog/product_attribute/save');
        $this->assertEquals('new string translation', $this->_translate('string to translate'));
    }

    /**
     * Return translation for a string literal belonging to backend area
     *
     * @param string $string
     * @return string
     */
    protected function _translate($string)
    {
        // emulate admin store and design
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\DesignInterface'
        )->setDesignTheme(
            1
        );
        /** @var \Magento\Framework\TranslateInterface $translate */
        $translate = $this->_objectManager->get('Magento\Framework\TranslateInterface');
        $translate->loadData(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE, true);
        return __($string);
    }

    /**
     * Get attribute data for post
     *
     * @return array
     */
    protected function _getAttributeData()
    {
        return array(
            'is_global' => \Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_STORE,
            'default_value_text' => '0',
            'default_value_yesno' => '0',
            'default_value_date' => '',
            'default_value_textarea' => '0',
            'is_required' => '1',
            'frontend_class' => '',
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
            'apply_to' => array('simple'),
            'frontend_label' => array(\Magento\Store\Model\Store::DEFAULT_STORE_ID => 'string to translate')
        );
    }
}
