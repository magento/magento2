<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Search\Block\Adminhtml\Reports;

/**
 * Adminhtml search report page content block
 *
 * @api
 * @since 100.0.2
 */
class Search extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Initialize Grid Container
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Magento_Search';
        $this->_controller = 'adminhtml_search';
        $this->_headerText = __('Search Terms');
        parent::_construct();
        $this->buttonList->remove('add');
    }
}
