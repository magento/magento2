<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Block\Adminhtml\Dashboard\AdvancedReporting;

use Magento\Ui\Test\Block\Adminhtml\Modal;

/**
 * Advanced Reporting block.
 */
class AdvancedReportingBlock extends Modal
{
    /**
     * Close pop-up button
     *
     * @var string
     */
    private $closeReportingButton = '[data-index="analytics_subscription_button_close"]';

    /**
     * @inheritdoc
     */
    public function isVisible()
    {
        $this->waitModalAnimationFinished();
        return parent::isVisible() && $this->_rootElement->find($this->closeReportingButton)->isVisible();
    }
}
