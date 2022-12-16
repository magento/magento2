<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product attribute add/edit form main tab
 *
 * @author Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab;

use Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Apply as HelperApply;
use Magento\Eav\Block\Adminhtml\Attribute\Edit\Main\AbstractMain;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\DataObject;

/**
 * Product attribute add/edit form main tab
 *
 * @api
 * @since 100.0.2
 */
class Main extends AbstractMain
{
    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();

        $this->removeUnusedFields();
        $this->processFrontendInputTypes();

        $this->_eventManager->dispatch('product_attribute_form_build_main_tab', ['form' => $this->getForm()]);

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function _getAdditionalElementTypes()
    {
        return ['apply' => HelperApply::class];
    }

    /**
     * Process frontend input types for product attributes
     *
     * @return void
     */
    private function processFrontendInputTypes(): void
    {
        $form = $this->getForm();
        /** @var AbstractElement $frontendInputElm */
        $frontendInputElm = $form->getElement('frontend_input');
        $additionalTypes = $this->getAdditionalFrontendInputTypes();

        $response = new DataObject();
        $response->setTypes([]);
        $this->_eventManager->dispatch('adminhtml_product_attribute_types', ['response' => $response]);
        $_hiddenFields = [];
        foreach ($response->getTypes() as $type) {
            $additionalTypes[] = $type;
            if (isset($type['hide_fields'])) {
                $_hiddenFields[$type['value']] = $type['hide_fields'];
            }
        }
        $this->_coreRegistry->register('attribute_type_hidden_fields', $_hiddenFields);

        $frontendInputValues = array_merge($frontendInputElm->getValues(), $additionalTypes);
        $frontendInputElm->setValues($frontendInputValues);
    }

    /**
     * Get additional Frontend Input Types for product attributes
     *
     * @return array
     */
    private function getAdditionalFrontendInputTypes(): array
    {
        $additionalTypes = [
            ['value' => 'price', 'label' => __('Price')],
            ['value' => 'media_image', 'label' => __('Media Image')],
        ];

        $additionalReadOnlyTypes = ['gallery' => __('Gallery')];
        $attributeObject = $this->getAttributeObject();
        if (isset($additionalReadOnlyTypes[$attributeObject->getFrontendInput()])) {
            $additionalTypes[] = [
                'value' => $attributeObject->getFrontendInput(),
                'label' => $additionalReadOnlyTypes[$attributeObject->getFrontendInput()],
            ];
        }

        return $additionalTypes;
    }

    /**
     * Remove unused form fields
     *
     * @return void
     */
    private function removeUnusedFields(): void
    {
        $form = $this->getForm();
        /* @var $fieldset Fieldset */
        $fieldset = $form->getElement('base_fieldset');
        $fieldsToRemove = ['attribute_code', 'is_unique', 'frontend_class'];
        foreach ($fieldset->getElements() as $element) {
            /** @var AbstractElement $element */
            if ($element->getId() && substr($element->getId(), 0, strlen('default_value')) === 'default_value') {
                $fieldsToRemove[] = $element->getId();
            }
        }
        foreach ($fieldsToRemove as $id) {
            $fieldset->removeField($id);
        }
    }
}
