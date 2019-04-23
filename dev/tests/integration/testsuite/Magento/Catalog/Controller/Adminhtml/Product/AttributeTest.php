<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Data\Form\FormKey;

/**
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
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
        $this->assertContains(
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
        $this->assertContains(
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
        $this->assertContains(
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
        $this->assertContains(
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
        $this->assertContains(
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
    public function testWrongAttributeCode()
    {
        $postData = $this->_getAttributeData() + ['attribute_code' => '_()&&&?'];
        $this->getRequest()->setPostValue($postData);
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('backend/catalog/product_attribute/save');
        $this->assertEquals(302, $this->getResponse()->getHttpResponseCode());
        $this->assertContains(
            'catalog/product_attribute/edit',
            $this->getResponse()->getHeader('Location')->getFieldValue()
        );
        /** @var \Magento\Framework\Message\Collection $messages */
        $messages = $this->_objectManager->create(\Magento\Framework\Message\ManagerInterface::class)->getMessages();
        $this->assertEquals(1, $messages->getCountByType('error'));
        /** @var \Magento\Framework\Message\Error $message */
        $message = $messages->getItemsByType('error')[0];
        $this->assertEquals(
            'Attribute code "_()&&&?" is invalid. Please use only letters (a-z),'
            . ' numbers (0-9) or underscore(_) in this field, first character should be a letter.',
            $message->getText()
        );
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
        $this->assertContains(
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
            'frontend_label' => [\Magento\Store\Model\Store::DEFAULT_STORE_ID => 'string to translate']
        ];
    }

    /**
     * Tests \Magento\Catalog\Controller\Adminhtml\Product\Attribute\Validate.
     *
     * @dataProvider dataProviderForTestValidate
     */
    public function testValidateAttribute($postData)
    {
        $expectedResult = ['error' => false];
        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $model */
        $model = $this->_objectManager->create(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);
        $model->load($postData['attribute_code'], 'attribute_code');
        $attributeId = $model->getId();

        $formKey = $this->_objectManager->get(FormKey::class);
        $postData['attribute_id'] = $attributeId;
        $postData['form_key'] = $formKey->getFormKey();
        $this->getRequest()->setPostValue($postData);
        $this->dispatch('backend/catalog/product_attribute/validate');
        $response = $this->getResponse()->getBody();
        $this->assertJson($response, 'Validate controller didn\'t return expected result.');
        $result = \Zend_Json::decode($response);
        $this->assertEquals($expectedResult, $result, 'Attribute validation didn\'t pass.');
    }

    /**
     * Returns data for testValidateAttribute.
     *
     * @return array
     */
    public function dataProviderForTestValidate()
    {
        return [
            'tax_class_id' => [
                'postData' => [
                    'attribute_code' => 'tax_class_id',
                    'frontend_label' => 'Tax Class',
                    'option' => [
                        'delete' => [
                            0 => '',
                            2 => '',
                        ],
                    ],
                ],
            ],
        ];
    }
}
