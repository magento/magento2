<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * description
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Attribute\Set\Toolbar;

use Magento\Framework\View\Element\AbstractBlock;

class Add extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'catalog/product/attribute/set/toolbar/add.phtml';

    /**
     * @return AbstractBlock
     */
    protected function _prepareLayout()
    {
        if ($this->getToolbar()) {
            $this->getToolbar()->addChild(
                'save_button',
                'Magento\Backend\Block\Widget\Button',
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
                'Magento\Backend\Block\Widget\Button',
                [
                    'label' => __('Back'),
                    'onclick' => 'setLocation(\'' . $this->getUrl('catalog/*/') . '\')',
                    'class' => 'back'
                ]
            );
        }

        $this->addChild('setForm', 'Magento\Catalog\Block\Adminhtml\Product\Attribute\Set\Main\Formset');
        return parent::_prepareLayout();
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    protected function _getHeader()
    {
        return __('Add New Attribute Set');
    }

    /**
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
