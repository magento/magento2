<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\ImportExport\Block\Adminhtml;

/**
 * Adminhtml import history page content block
 *
 * @api
 * @since 100.0.2
 */
class History extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->removeButton('add');
    }
}
