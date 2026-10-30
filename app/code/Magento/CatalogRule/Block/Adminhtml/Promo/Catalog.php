<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\CatalogRule\Block\Adminhtml\Promo;

/**
 * Catalog price rules
 *
 * @api
 * @since 100.0.2
 */
class Catalog extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Initialise the block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Magento_CatalogRule';
        $this->_controller = 'adminhtml_promo_catalog';
        $this->_headerText = __('Catalog Price Rule');
        $this->_addButtonLabel = __('Add New Rule');
        parent::_construct();

        $this->buttonList->add(
            'apply_rules',
            [
                'label' => __('Apply Rules'),
                'onclick' => "location.href='" . $this->getUrl('catalog_rule/*/applyRules') . "'",
                'class' => 'apply'
            ]
        );
    }
}
