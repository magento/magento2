<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Block\Adminhtml\Refresh;

/**
 * Report Refresh statistic container
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Statistics extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Modify Header and remove button "Add"
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Magento_Reports';
        $this->_controller = 'adminhtml_refresh_statistics';
        $this->_headerText = __('Refresh Statistics');
        parent::_construct();
        $this->buttonList->remove('add');
    }
}
