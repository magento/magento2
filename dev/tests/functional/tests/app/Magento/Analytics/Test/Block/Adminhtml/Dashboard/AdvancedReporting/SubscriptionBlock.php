<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Block\Adminhtml\Dashboard\AdvancedReporting;

use Magento\Mtf\Client\Locator;
use Magento\Ui\Test\Block\Adminhtml\Modal;

/**
 * Subscription block.
 */
class SubscriptionBlock extends Modal
{
    /**
     * Close subscription pop-up button
     *
     * @var string
     */
    private $closeReportingButton = '[data-index="analytics_subscription_button_close"]';

    /**
     * Skip subscription popup.
     *
     * @return void
     */
    public function skipAdvancedReporting()
    {
        $this->waitModalAnimationFinished();
        $this->_rootElement->find($this->closeReportingButton)->click();
        $this->waitForElementNotVisible($this->loadingMask);
    }
}
