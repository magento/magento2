<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product attributes tab
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Attribute\NewAttribute\Product;

use Magento\Backend\Block\Widget\Form;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Attributes extends \Magento\Catalog\Block\Adminhtml\Form
{
    /**
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
     * @return string
     */
    protected function _toHtml()
    {
        parent::_toHtml();
        return $this->getForm()->getElement('group_fields')->getChildrenHtml();
    }
}
