<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Block\Adminhtml\Product\Attribute\Set\Toolbar;

/**
 * Adminhtml catalog product sets main page toolbar
 *
 * @api
 * @since 100.0.2
 */
class Main extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Catalog::catalog/product/attribute/set/toolbar/main.phtml';

    /**
     * Prepare the layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->getToolbar()->addChild(
            'addButton',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'label' => __('Add Attribute Set'),
                'onclick' => 'setLocation(\'' . $this->getUrl('catalog/*/add') . '\')',
                'class' => 'add primary add-set'
            ]
        );
        return parent::_prepareLayout();
    }

    /**
     * Return HTML for the new button
     *
     * @return string
     */
    public function getNewButtonHtml()
    {
        return $this->getChildHtml('addButton');
    }

    /**
     * Return header text
     *
     * @return \Magento\Framework\Phrase
     */
    protected function _getHeader()
    {
        return __('Attribute Sets');
    }

    /**
     * Return HTML for this block
     *
     * @return string
     */
    protected function _toHtml()
    {
        $this->_eventManager->dispatch(
            'adminhtml_catalog_product_attribute_set_toolbar_main_html_before',
            ['block' => $this]
        );
        return parent::_toHtml();
    }
}
