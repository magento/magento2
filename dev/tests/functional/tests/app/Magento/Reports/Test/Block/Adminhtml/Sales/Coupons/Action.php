<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Reports\Test\Block\Adminhtml\Sales\Coupons;

use Magento\Backend\Test\Block\GridPageActions;

/**
 * Class Action
 * Action block for Coupons Views Report
 */
class Action extends GridPageActions
{
    /**
     * Show Report button
     *
     * @var string
     */
    protected $showReportButton = '#filter_form_submit';

    /**
     * Show report button click
     *
     * @return void
     */
    public function showReport()
    {
        $this->_rootElement->find($this->showReportButton)->click();
    }
}
