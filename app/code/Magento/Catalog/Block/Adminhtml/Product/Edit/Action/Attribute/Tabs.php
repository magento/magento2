<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Action\Attribute;

/**
 * Adminhtml catalog product edit action attributes update tabs block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('attributes_update_tabs');
        $this->setDestElementId('attributes-edit-form');
        $this->setTitle(__('Products Information'));
    }
}
