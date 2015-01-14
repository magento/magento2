<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Adminhtml\Category\Tab;

class Design extends \Magento\Catalog\Block\Adminhtml\Form
{
    /**
     * @var array|null
     */
    protected $_category;

    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setShowGlobalIcon(true);
    }

    /**
     * @return array|null
     */
    public function getCategory()
    {
        if (!$this->_category) {
            $this->_category = $this->_coreRegistry->registry('category');
        }
        return $this->_category;
    }

    /**
     * @return void
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setDataObject($this->getCategory());

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Custom Design')]);

        $this->_setFieldset($this->getCategory()->getDesignAttributes(), $fieldset);

        $form->addValues($this->getCategory()->getData());
        $form->setFieldNameSuffix('general');
        $this->setForm($form);
    }
}
