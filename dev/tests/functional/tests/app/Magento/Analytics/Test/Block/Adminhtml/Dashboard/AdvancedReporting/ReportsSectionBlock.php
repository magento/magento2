<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Block\Adminhtml\Dashboard\AdvancedReporting;

use Magento\Mtf\Block\Block;

/**
 * Advanced Reporting section on dashboard.
 */
class ReportsSectionBlock extends Block
{
    /**
     * Advanced Reporting button on dashboard.
     *
     * @var string
     */
    protected $advancedReportingButton = '[data-index="analytics-service-link"]';

    /**
     * Click Advanced Reporting link.
     *
     * @return void
     */
    public function click()
    {
        $this->_rootElement->find($this->advancedReportingButton)->click();
    }
}
