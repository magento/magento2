<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Block\Adminhtml\Sales\TaxRule;

use Magento\Backend\Test\Block\PageActions;

/**
 * Class Action
 * Action block for Tax Report
 */
class Action extends PageActions
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
