<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Block\Adminhtml\Integration\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Initialize integration edit page tabs
     *
     * @return void
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('integration_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Basic Settings'));
    }
}
