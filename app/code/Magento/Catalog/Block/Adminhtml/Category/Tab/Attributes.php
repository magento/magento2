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


/**
 * Adminhtml Catalog Category Attributes per Group Tab block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Category\Tab;

class Attributes extends \Magento\Backend\Block\Widget\Form\Generic
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
            array('legend' => __($group->getAttributeGroupName()), 'class' => 'fieldset-wide')
        );

        if ($this->getAddHiddenFields()) {
            if (!$this->getCategory()->getId()) {
                // path
                if ($this->getRequest()->getParam('parent')) {
                    $fieldset->addField(
                        'path',
                        'hidden',
                        array('name' => 'path', 'value' => $this->getRequest()->getParam('parent'))
                    );
                } else {
                    $fieldset->addField('path', 'hidden', array('name' => 'path', 'value' => 1));
                }
            } else {
                $fieldset->addField('id', 'hidden', array('name' => 'id', 'value' => $this->getCategory()->getId()));
                $fieldset->addField(
                    'path',
                    'hidden',
                    array('name' => 'path', 'value' => $this->getCategory()->getPath())
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

        $this->_eventManager->dispatch('adminhtml_catalog_category_edit_prepare_form', array('form' => $form));

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
        return array(
            'image' => 'Magento\Catalog\Block\Adminhtml\Category\Helper\Image',
            'textarea' => 'Magento\Catalog\Block\Adminhtml\Helper\Form\Wysiwyg'
        );
    }
}
