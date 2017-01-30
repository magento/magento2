<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Adminhtml Catalog Category Attributes per Group Tab block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Category\Tab;

class Attributes extends \Magento\Catalog\Block\Adminhtml\Form
{
    /**
     * Retrieve Category object
     *
     * @return \Magento\Catalog\Model\Category
     */
    public function getCategory()
    {
        return $this->_coreRegistry->registry('current_category');
    }

    /**
     * Initialize tab
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setShowGlobalIcon(true);
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _prepareForm()
    {
        $group = $this->getGroup();
        $attributes = $this->getAttributes();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('group_' . $group->getId());
        $form->setDataObject($this->getCategory());

        $fieldset = $form->addFieldset(
            'fieldset_group_' . $group->getId(),
            ['legend' => __($group->getAttributeGroupName()), 'class' => 'fieldset-wide']
        );

        if ($this->getAddHiddenFields()) {
            if (!$this->getCategory()->getId()) {
                // path
                if ($this->getRequest()->getParam('parent')) {
                    $fieldset->addField(
                        'path',
                        'hidden',
                        ['name' => 'path', 'value' => $this->getRequest()->getParam('parent')]
                    );
                } else {
                    $fieldset->addField('path', 'hidden', ['name' => 'path', 'value' => 1]);
                }
            } else {
                $fieldset->addField('id', 'hidden', ['name' => 'id', 'value' => $this->getCategory()->getId()]);
                $fieldset->addField(
                    'path',
                    'hidden',
                    ['name' => 'path', 'value' => $this->getCategory()->getPath()]
                );
            }
        }

        $this->_setFieldset($attributes, $fieldset);

        if ($this->getCategory()->getLevel() == 1) {
            $fieldset->removeField('custom_use_parent_settings');
        } else {
            if ($this->getCategory()->getCustomUseParentSettings()) {
                foreach ($this->getCategory()->getDesignAttributes() as $attribute) {
                    if ($element = $form->getElement($attribute->getAttributeCode())) {
                        $element->setDisabled(true);
                    }
                }
            }
            if ($element = $form->getElement('custom_use_parent_settings')) {
                $element->setData('onchange', 'onCustomUseParentChanged(this)');
            }
        }

        if ($this->getCategory()->hasLockedAttributes()) {
            foreach ($this->getCategory()->getLockedAttributes() as $attribute) {
                if ($element = $form->getElement($attribute)) {
                    $element->setReadonly(true, true);
                }
            }
        }

        if (!$this->getCategory()->getId()) {
            $this->getCategory()->setIncludeInMenu(1);
        }

        $form->addValues($this->getCategory()->getData());

        $this->_eventManager->dispatch('adminhtml_catalog_category_edit_prepare_form', ['form' => $form]);

        $form->setFieldNameSuffix('general');
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Retrieve Additional Element Types
     *
     * @return array
     */
    protected function _getAdditionalElementTypes()
    {
        return [
            'image' => 'Magento\Catalog\Block\Adminhtml\Category\Helper\Image',
            'textarea' => 'Magento\Catalog\Block\Adminhtml\Helper\Form\Wysiwyg'
        ];
    }
}
