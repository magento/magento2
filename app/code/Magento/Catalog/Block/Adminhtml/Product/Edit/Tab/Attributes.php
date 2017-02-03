<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product attributes tab
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Tab;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Attributes extends \Magento\Catalog\Block\Adminhtml\Form
{
    /**
     * Prepare attributes form
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /** @var $group \Magento\Eav\Model\Entity\Attribute\Group */
        $group = $this->getGroup();
        if ($group) {
            /** @var \Magento\Framework\Data\Form $form */
            $form = $this->_formFactory->create();
            $product = $this->_coreRegistry->registry('product');
            $isWrapped = $this->_coreRegistry->registry('use_wrapper');
            if (!isset($isWrapped)) {
                $isWrapped = true;
            }
            $isCollapsable = $isWrapped && $group->getAttributeGroupCode() == 'product-details';
            $legend = $isWrapped ? __($group->getAttributeGroupName()) : null;
            // Initialize product object as form property to use it during elements generation
            $form->setDataObject($product);

            $fieldset = $form->addFieldset(
                'group-fields-' . $group->getAttributeGroupCode(),
                ['class' => 'user-defined', 'legend' => $legend, 'collapsable' => $isCollapsable]
            );

            $attributes = $this->getGroupAttributes();

            $this->_setFieldset($attributes, $fieldset, ['gallery']);

            $tierPrice = $form->getElement('tier_price');
            if ($tierPrice) {
                $tierPrice->setRenderer(
                    $this->getLayout()->createBlock('Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Price\Tier')
                );
            }

            // Add new attribute controls if it is not an image tab
            if (!$form->getElement(
                'media_gallery'
            ) && $this->_authorization->isAllowed(
                'Magento_Catalog::attributes_attributes'
            ) && $isWrapped
            ) {
                $attributeCreate = $this->getLayout()->createBlock(
                    'Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes\Create'
                );

                $attributeCreate->getConfig()->setAttributeGroupCode(
                    $group->getAttributeGroupCode()
                )->setTabId(
                    'group_' . $group->getId()
                )->setGroupId(
                    $group->getId()
                )->setStoreId(
                    $form->getDataObject()->getStoreId()
                )->setAttributeSetId(
                    $form->getDataObject()->getAttributeSetId()
                )->setTypeId(
                    $form->getDataObject()->getTypeId()
                )->setProductId(
                    $form->getDataObject()->getId()
                );

                $attributeSearch = $this->getLayout()->createBlock(
                    'Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes\Search'
                )->setGroupId(
                    $group->getId()
                )->setGroupCode(
                    $group->getAttributeGroupCode()
                );

                $attributeSearch->setAttributeCreate($attributeCreate->toHtml());

                $fieldset->setHeaderBar($attributeSearch->toHtml());
            }

            $values = $product->getData();

            // Set default attribute values for new product or on attribute set change
            if (!$product->getId() || $product->dataHasChangedFor('attribute_set_id')) {
                foreach ($attributes as $attribute) {
                    if (!isset($values[$attribute->getAttributeCode()])) {
                        $values[$attribute->getAttributeCode()] = $attribute->getDefaultValue();
                    }
                }
            }

            if ($product->hasLockedAttributes()) {
                foreach ($product->getLockedAttributes() as $attribute) {
                    $element = $form->getElement($attribute);
                    if ($element) {
                        $element->setReadonly(true, true);
                        $element->lock();
                    }
                }
            }

            $form->addValues($values);
            $form->setFieldNameSuffix('product');

            $this->_eventManager->dispatch(
                'adminhtml_catalog_product_edit_prepare_form',
                ['form' => $form, 'layout' => $this->getLayout()]
            );

            $this->setForm($form);
        }
    }

    /**
     * Retrieve additional element types
     *
     * @return array
     */
    protected function _getAdditionalElementTypes()
    {
        $result = [
            'price' => 'Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Price',
            'weight' => 'Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Weight',
            'gallery' => 'Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery',
            'image' => 'Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Image',
            'boolean' => 'Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Boolean',
            'textarea' => 'Magento\Catalog\Block\Adminhtml\Helper\Form\Wysiwyg',
        ];

        $response = new \Magento\Framework\DataObject();
        $response->setTypes([]);
        $this->_eventManager->dispatch('adminhtml_catalog_product_edit_element_types', ['response' => $response]);

        foreach ($response->getTypes() as $typeName => $typeClass) {
            $result[$typeName] = $typeClass;
        }

        return $result;
    }
}
