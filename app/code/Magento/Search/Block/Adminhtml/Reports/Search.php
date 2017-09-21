<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
