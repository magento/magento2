<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Block\Adminhtml\Product\Attribute\NewAttribute\Product;

use Magento\Backend\Block\Widget\Form;

/**
 * Product attributes tab
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Attributes extends \Magento\Catalog\Block\Adminhtml\Form
{
    /**
     * Prepare the form
     *
     * @return void
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        /**
         * Initialize product object as form property
         * for using it in elements generation
         */
        $form->setDataObject($this->_coreRegistry->registry('product'));

        $fieldset = $form->addFieldset('group_fields', []);

        $attributes = $this->getGroupAttributes();

        $this->_setFieldset($attributes, $fieldset, ['gallery']);

        $values = $this->_coreRegistry->registry('product')->getData();
        /**
         * Set attribute default values for new product
         */
        if (!$this->_coreRegistry->registry('product')->getId()) {
            foreach ($attributes as $attribute) {
                if (!isset($values[$attribute->getAttributeCode()])) {
                    $values[$attribute->getAttributeCode()] = $attribute->getDefaultValue();
                }
            }
        }

        $this->_eventManager->dispatch('adminhtml_catalog_product_edit_prepare_form', ['form' => $form]);
        $form->addValues($values);
        $form->setFieldNameSuffix('product');
        $this->setForm($form);
    }

    /**
     * Return an array of additional element types
     *
     * @return array
     */
    protected function _getAdditionalElementTypes()
    {
        $result = [
            'price' => \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Price::class,
            'image' => \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Image::class,
            'boolean' => \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Boolean::class,
        ];

        $response = new \Magento\Framework\DataObject();
        $response->setTypes([]);
        $this->_eventManager->dispatch('adminhtml_catalog_product_edit_element_types', ['response' => $response]);

        foreach ($response->getTypes() as $typeName => $typeClass) {
            $result[$typeName] = $typeClass;
        }

        return $result;
    }

    /**
     * Return HTML for this block
     *
     * @return string
     */
    protected function _toHtml()
    {
        parent::_toHtml();
        return $this->getForm()->getElement('group_fields')->getChildrenHtml();
    }
}
