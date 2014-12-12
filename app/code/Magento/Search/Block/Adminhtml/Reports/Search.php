<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Search\Block\Adminhtml\Reports;

/**
 * Adminhtml search report page content block
 *
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
