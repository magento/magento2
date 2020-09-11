<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Request\Http as HttpRequest;

/**
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributeTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * @return void
     */
    public function testWrongFrontendInput()
    {
        $postData = array_merge(
            $this->_getAttributeData(),
            [
                'attribute_id' => 100500,
                'frontend_input' => 'some_input',
            ]
        );
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($postData);
        $this->dispatch('backend/catalog/product_attribute/save');
        $this->assertEquals(302, $this->getResponse()->getHttpResponseCode());
        $this->assertStringContainsString(
            'catalog/product_attribute/edit/attribute_id/100500',
            $this->getResponse()->getHeader('Location')->getFieldValue()
        );
        /** @var \Magento\Framework\Message\Collection $messages */
        $messages = $this->_objectManager->create(\Magento\Framework\Message\ManagerInterface::class)->getMessages();
        $this->assertEquals(1, $messages->getCountByType('error'));
        $message = $messages->getItemsByType('error')[0];
        $this->assertEquals('Input type "some_input" not found in the input types list.', $message->getText());
    }

    /**
     * @magentoDataFixture Magento/Catalog/controllers/_files/attribute_system_popup.php
     * @return void
     */
    public function testWithPopup()
    {
        $postData = $this->_getAttributeData() + [
            'attribute_id' => 5,
            'popup' => 'true',
            'new_attribute_set_name' => 'new_attribute_set',
        ];
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($postData);
        $this->dispatch('backend/catalog/product_attribute/save');
        $this->assertEquals(302, $this->getResponse()->getHttpResponseCode());
        $this->assertStringContainsString(
            'catalog/product/addAttribute/attribute/5',
            $this->getResponse()->getHeader('Location')->getFieldValue()
        );
        /** @var \Magento\Framework\Message\Collection $messages */
        $messages = $this->_objectManager->create(\Magento\Framework\Message\ManagerInterface::class)->getMessages();
        $this->assertEquals(1, $messages->getCountByType('success'));
        $message = $messages->getItemsByType('success')[0];
        $this->assertEquals('You saved the product attribute.', $message->getText());
    }

    /**
     * @return void
     */
    public function testWithExceptionWhenSaveAttribute()
    {
        $postData = $this->_getAttributeData() + ['attribute_id' => 0, 'frontend_input' => 'boolean'];
        $this->getRequest()->setPostValue($postData);
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('backend/catalog/product_attribute/save');
        $this->assertEquals(302, $this->getResponse()->getHttpResponseCode());
        $this->assertStringContainsString(
            'catalog/product_attribute/edit/attribute_id/0',
            $this->getResponse()->getHeader('Location')->getFieldValue()
        );
        /** @var \Magento\Framework\Message\Collection $messages */
        $messages = $this->_objectManager->create(\Magento\Framework\Message\ManagerInterface::class)->getMessages();
        $this->assertEquals(1, $messages->getCountByType('error'));
    }

    /**
     * @return void
     */
    public function testWrongAttributeId()
    {
        $postData = $this->_getAttributeData() + ['attribute_id' => 100500];
        $this->getRequest()->setPostValue($postData);
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('backend/catalog/product_attribute/save');
        $this->assertEquals(302, $this->getResponse()->getHttpResponseCode());
        $this->assertStringContainsString(
            'catalog/product_attribute/index',
            $this->getResponse()->getHeader('Location')->getFieldValue()
        );
        /** @var \Magento\Framework\Message\Collection $messages */
        $messages = $this->_objectManager->create(\Magento\Framework\Message\ManagerInterface::class)->getMessages();
        $this->assertEquals(1, $messages->getCountByType('error'));
        /** @var \Magento\Framework\Message\Error $message */
        $message = $messages->getItemsByType('error')[0];
        $this->assertEquals('This attribute no longer exists.', $message->getText());
    }

    /**
     * @return void
     */
    public function testAttributeWithoutId()
    {
        $postData = $this->_getAttributeData() + [
                'attribute_code' => uniqid('attribute_'),
                'set' => 4,
                'frontend_input' => 'boolean',
            ];
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($postData);
        $this->dispatch('backend/catalog/product_attribute/save');
        $this->assertEquals(302, $this->getResponse()->getHttpResponseCode());
        $this->assertStringContainsString(
            'catalog/product_attribute/index',
            $this->getResponse()->getHeader('Location')->getFieldValue()
        );
        /** @var \Magento\Framework\Message\Collection $messages */
        $messages = $this->_objectManager->create(\Magento\Framework\Message\ManagerInterface::class)->getMessages();
        $this->assertEquals(1, $messages->getCountByType('success'));
        /** @var \Magento\Framework\Message\Success $message */
        $message = $messages->getItemsByType('success')[0];
        $this->assertEquals('You saved the product attribute.', $message->getText());
    }

    /**
     * @return void
     */
    public function testAttributeWithoutEntityTypeId()
    {
        $postData = $this->_getAttributeData() + ['attribute_id' => '2', 'new_attribute_set_name' => ' '];
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($postData);
        $this->dispatch('backend/catalog/product_attribute/save');
        $this->assertEquals(302, $this->getResponse()->getHttpResponseCode());
        $this->assertStringContainsString(
            'catalog/product_attribute/index',
            $this->getResponse()->getHeader('Location')->getFieldValue()
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/controllers/_files/attribute_system.php
     */
    public function testSaveActionApplyToDataSystemAttribute()
    {
        $postData = $this->_getAttributeData() + ['attribute_id' => '2'];
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($postData);
        $this->dispatch('backend/catalog/product_attribute/save');
        $model = $this->_objectManager->create(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);
        $model->load($postData['attribute_id']);
        $this->assertNull($model->getData('apply_to'));
    }

    /**
     * @magentoDataFixture Magento/Catalog/controllers/_files/attribute_user_defined.php
     */
    public function testSaveActionApplyToDataUserDefinedAttribute()
    {
        $postData = $this->_getAttributeData() + ['attribute_id' => '1'];
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($postData);
        $this->dispatch('backend/catalog/product_attribute/save');
        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $model */
        $model = $this->_objectManager->create(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);
        $model->load($postData['attribute_id']);
        $this->assertEquals('simple', $model->getData('apply_to'));
    }

    /**
     * @magentoDataFixture Magento/Catalog/controllers/_files/attribute_system_with_applyto_data.php
     */
    public function testSaveActionApplyToData()
    {
        $postData = $this->_getAttributeData() + ['attribute_id' => '3'];
        unset($postData['apply_to']);
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($postData);
        $this->dispatch('backend/catalog/product_attribute/save');
        $model = $this->_objectManager->create(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);
        $model->load($postData['attribute_id']);
        $this->assertEquals(['simple'], $model->getApplyTo());
    }

    /**
     * @magentoDataFixture Magento/Translation/_files/db_translate_admin_store.php
     * @magentoDataFixture Magento/Catalog/controllers/_files/attribute_user_defined.php
     * @magentoAppIsolation enabled
     */
    public function testSaveActionCleanAttributeLabelCache()
    {
        /** @var \Magento\Translation\Model\ResourceModel\StringUtils $string */
        $string = $this->_objectManager->create(\Magento\Translation\Model\ResourceModel\StringUtils::class);
        $this->assertEquals('predefined string translation', $this->_translate('string to translate'));
        $string->saveTranslate('string to translate', 'new string translation');
        $postData = $this->_getAttributeData() + ['attribute_id' => 1];
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($postData);
        $this->dispatch('backend/catalog/product_attribute/save');
        $this->assertEquals('new string translation', $this->_translate('string to translate'));
    }

    /**
     * Get attribute data preset.
     *
     * @return array
     */
    private function getLargeOptionsSetAttributeData()
    {
        return [
            'frontend_label' => [
                0 => 'testdrop1',
                1 => '',
                2 => '',
            ],
            'frontend_input' => 'select',
            'is_required' => '0',
            'update_product_preview_image' => '0',
            'use_product_image_for_swatch' => '0',
            'visual_swatch_validation' => '',
            'visual_swatch_validation_unique' => '',
            'text_swatch_validation' => '',
            'text_swatch_validation_unique' => '',
            'attribute_code' => 'test_many_options',
            'is_global' => '0',
            'default_value_text' => '',
            'default_value_yesno' => '0',
            'default_value_date' => '',
            'default_value_textarea' => '',
            'is_unique' => '0',
            'is_used_in_grid' => '1',
            'is_visible_in_grid' => '1',
            'is_filterable_in_grid' => '1',
            'is_searchable' => '0',
            'is_comparable' => '0',
            'is_filterable' => '0',
            'is_filterable_in_search' => '0',
            'is_used_for_promo_rules' => '0',
            'is_html_allowed_on_front' => '1',
            'is_visible_on_front' => '0',
            'used_in_product_listing' => '0',
            'used_for_sort_by' => '0',
            'swatch_input_type' => 'dropdown',
        ];
    }

    /**
     * Test attribute saving with large amount of options exceeding maximum allowed by max_input_vars limit.
     * @return void
     */
    public function testLargeOptionsDataSet()
    {
        $maxInputVars = ini_get('max_input_vars');
        // Each option is at least 4 variables array (order, admin value, first store view value, delete flag).
        // Set options count to exceed max_input_vars by 100 options (400 variables).
        $optionsCount = floor($maxInputVars / 4) + 100;
        $attributeData = $this->getLargeOptionsSetAttributeData();
        $optionsData = [];
        $expectedOptionsLabels = [];
        for ($i = 0; $i < $optionsCount; $i++) {
            $expectedOptionLabelOnStoreView = 'value_' . $i . '_store_1';
            $expectedOptionsLabels[$i+1] = $expectedOptionLabelOnStoreView;
            $optionId = 'option_' . $i;
            $optionRowData = [];
            $optionRowData['option']['order'][$optionId] = $i + 1;
            $optionRowData['option']['value'][$optionId][0] = 'value_' . $i . '_admin';
            $optionRowData['option']['value'][$optionId][1] = $expectedOptionLabelOnStoreView;
            $optionRowData['option']['delete'][$optionId] = '';
            $optionsData[] = http_build_query($optionRowData);
        }
        $attributeData['serialized_options'] = json_encode($optionsData);
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($attributeData);
        $this->dispatch('backend/catalog/product_attribute/save');
        $entityTypeId = $this->_objectManager->create(
            \Magento\Eav\Model\Entity::class
        )->setType(
            \Magento\Catalog\Model\Product::ENTITY
        )->getTypeId();

        /** @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
        $attribute = $this->_objectManager->create(
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class
        )->setEntityTypeId(
            $entityTypeId
        );
        try {
            $attribute->loadByCode($entityTypeId, 'test_many_options');
            $options = $attribute->getOptions();
            // assert that all options are saved without truncation
            $this->assertEquals(
                $optionsCount + 1,
                count($options),
                'Expected options count does not match (regarding first empty option for non-required attribute)'
            );

            foreach ($expectedOptionsLabels as $optionOrderNum => $label) {
                $this->assertEquals(
                    $label,
                    $options[$optionOrderNum]->getLabel(),
                    "Label for option #{$optionOrderNum} does not match expected."
                );
            }
        } catch (LocalizedException $e) {
            $this->fail('Test failed with exception on attribute model load: ' . $e);
        }
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
            \Magento\Framework\View\DesignInterface::class
        )->setDesignTheme(
            1
        );
        /** @var \Magento\Framework\TranslateInterface $translate */
        $translate = $this->_objectManager->get(\Magento\Framework\TranslateInterface::class);
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
        return [
            'is_global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'default_value_text' => '0',
            'default_value_yesno' => '0',
            'default_value_date' => '',
            'default_value_textarea' => '0',
            'is_required' => '1',
            'frontend_class' => '',
            'frontend_input' => 'select',
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
            'apply_to' => ['simple'],
            'frontend_label' => [\Magento\Store\Model\Store::DEFAULT_STORE_ID => 'string to translate'],
        ];
    }
}
