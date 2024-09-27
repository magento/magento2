<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Block\Adminhtml\Product\Attribute\Set\Toolbar;

use Magento\Framework\View\Element\AbstractBlock;

class Add extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Catalog::catalog/product/attribute/set/toolbar/add.phtml';

    /**
     * Prepare the layout
     *
     * @return AbstractBlock
     */
    protected function _prepareLayout()
    {
        if ($this->getToolbar()) {
            $this->getToolbar()->addChild(
                'save_button',
                \Magento\Backend\Block\Widget\Button::class,
                [
                    'label' => __('Save'),
                    'class' => 'save primary save-attribute-set',
                    'data_attribute' => [
                        'mage-init' => ['button' => ['event' => 'save', 'target' => '#set-prop-form']],
                    ]
                ]
            );
            $this->getToolbar()->addChild(
                'back_button',
                \Magento\Backend\Block\Widget\Button::class,
                [
                    'label' => __('Back'),
                    'onclick' => 'setLocation(\'' . $this->getUrl('catalog/*/') . '\')',
                    'class' => 'back'
                ]
            );
        }

        $this->addChild('setForm', \Magento\Catalog\Block\Adminhtml\Product\Attribute\Set\Main\Formset::class);
        return parent::_prepareLayout();
    }

    /**
     * Return header text
     *
     * @return \Magento\Framework\Phrase
     */
    protected function _getHeader()
    {
        return __('Add New Attribute Set');
    }

    /**
     * Return HTML for the form
     *
     * @return string
     */
    public function getFormHtml()
    {
        return $this->getChildHtml('setForm');
    }

    /**
     * Return id of form, used by this block
     *
     * @return string
     */
    public function getFormId()
    {
        return $this->getChildBlock('setForm')->getForm()->getId();
    }
}
