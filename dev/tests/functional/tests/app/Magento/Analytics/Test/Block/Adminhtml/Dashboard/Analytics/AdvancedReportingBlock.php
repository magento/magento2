<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Block\Adminhtml\Dashboard\Analytics;

use Magento\Mtf\Block\Block;

/**
 * Advanced Reporting section
 */
class AdvancedReportingBlock extends Block
{
    /**
     * Advanced Reporting Button.
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
