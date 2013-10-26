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
 * @package     Magento_Adminhtml
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


namespace Magento\Adminhtml\Controller\Catalog\Product;

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
        $this->dispatch('backend/admin/catalog_product_attribute/save');
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
        $this->dispatch('backend/admin/catalog_product_attribute/save');
        $model = $this->_objectManager->create('Magento\Catalog\Model\Resource\Eav\Attribute');
        $model->load($postData['attribute_id']);
        $this->assertEquals('simple,configurable', $model->getData('apply_to'));
    }

    /**
     * @magentoDataFixture Magento/Catalog/controllers/_files/attribute_system_with_applyto_data.php
     */
    public function testSaveActionApplyToData()
    {
        $postData = $this->_getAttributeData() + array('attribute_id' => '3');
        unset($postData['apply_to']);
        $this->getRequest()->setPost($postData);
        $this->dispatch('backend/admin/catalog_product_attribute/save');
        $model = $this->_objectManager->create('Magento\Catalog\Model\Resource\Eav\Attribute');
        $model->load($postData['attribute_id']);
        $this->assertEquals(array('simple', 'configurable'), $model->getApplyTo());
    }

    /**
     * @magentoDataFixture Magento/Core/_files/db_translate_admin_store.php
     * @magentoDataFixture Magento/Adminhtml/controllers/_files/cache/all_types_enabled.php
     * @magentoDataFixture Magento/Catalog/controllers/_files/attribute_user_defined.php
     * @magentoAppIsolation enabled
     */
    public function testSaveActionCleanAttributeLabelCache()
    {
        // ensure string translation is cached
        $this->_translate('Fixture String');
        /** @var \Magento\Core\Model\Resource\Translate\String $translateString */
        $translateString = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Core\Model\Resource\Translate\String');
        $translateString->saveTranslate(
            'Fixture String', 'New Db Translation', 'en_US', \Magento\Core\Model\AppInterface::ADMIN_STORE_ID
        );
        $this->assertEquals(
            'Fixture Db Translation', $this->_translate('Fixture String'), 'Translation is expected to be cached'
        );

        $postData = $this->_getAttributeData() + array('attribute_id' => 1);
        $this->getRequest()->setPost($postData);
        $this->dispatch('backend/admin/catalog_product_attribute/save');

        $this->assertEquals(
            'New Db Translation', $this->_translate('Fixture String'), 'Translation cache is expected to be flushed'
        );
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
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Model\StoreManagerInterface')
            ->setCurrentStore(\Magento\Core\Model\AppInterface::ADMIN_STORE_ID);
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\View\DesignInterface')
            ->setDesignTheme(1);
        /** @var \Magento\Core\Model\Translate $translate */
        $translate = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Core\Model\Translate');
        $translate->init(\Magento\Backend\Helper\Data::BACKEND_AREA_CODE, null);
        return $translate->translate(array($string));
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
            'apply_to' => array('simple', 'configurable'),
            'frontend_label' => array(
                \Magento\Core\Model\AppInterface::ADMIN_STORE_ID => 'Fixture String',
            ),
        );
    }
}
