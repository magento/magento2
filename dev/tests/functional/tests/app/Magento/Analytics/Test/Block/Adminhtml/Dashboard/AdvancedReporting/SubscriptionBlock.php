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
     * Modal checkbox
     *
     * @var string
     */
    private $checkbox = '[name="analytics_subscription_checkbox"]';

    /**
     * Enable Advanced Reporting button
     *
     * @var string
     */
    private $acceptReportingButton = '[data-index="analytics_subscription_button_accept"]';

    /**
     * Disable Advanced Reporting button
     *
     * @var string
     */
    private $declineReportingButton = '[data-index="analytics_subscription_button_decline"]';

    /**
     * Skip subscription pop-up button
     *
     * @var string
     */
    private $skipReportingButton = '[data-index="analytics_subscription_button_postpone"]';

    /**
     * Enable checkbox in modal window.
     *
     * @return void
     */
    public function enableCheckbox()
    {
        $this->_rootElement->find($this->checkbox, Locator::SELECTOR_CSS, 'checkbox')->setValue('Yes');
    }

    /**
     * Disable checkbox in modal window.
     *
     * @return void
     */
    public function disableCheckbox()
    {
        $this->_rootElement->find($this->checkbox, Locator::SELECTOR_CSS, 'checkbox')->setValue('No');
    }

    /**
     * Enable Advanced Reporting on a subscription popup.
     *
     * @return void
     */
    public function acceptAdvancedReporting()
    {
        $this->waitModalAnimationFinished();
        $this->_rootElement->find($this->acceptReportingButton)->click();
        $this->waitForElementNotVisible($this->loadingMask);
    }

    /**
     * Disable Advanced Reporting on a subscription popup.
     *
     * @return void
     */
    public function declineAdvancedReporting()
    {
        $this->waitModalAnimationFinished();
        $this->_rootElement->find($this->declineReportingButton)->click();
        $this->waitForElementNotVisible($this->loadingMask);
    }

    /**
     * Skip subscription popup.
     *
     * @return void
     */
    public function skipAdvancedReporting()
    {
        $this->waitModalAnimationFinished();
        $this->_rootElement->find($this->skipReportingButton)->click();
        $this->waitForElementNotVisible($this->loadingMask);
    }
}
