<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Sales\Test\Block\Adminhtml\Order\Creditmemo;

use Magento\Mtf\Client\Locator;

/**
 * Invoice totals block.
 */
class Totals extends \Magento\Sales\Test\Block\Adminhtml\Order\Totals
{
    /**
     * Submit invoice button selector.
     *
     * @var string
     */
    protected $submit = '[data-ui-id="order-items-submit-button"]';

    /**
     * Capture amount select selector.
     *
     * @var string
     */
    protected $capture = '[name="invoice[capture_case]"]';

    /**
     * Refund Shipping css selector.
     *
     * @var string
     */
    private $refundShippingSelector = '#shipping_amount';

    /**
     * Adjustment Refund css selector.
     *
     * @var string
     */
    private $adjustmentRefundSelector = '#adjustment_positive';

    /**
     * Adjustment Fee css selector.
     *
     * @var string
     */
    private $adjustmentFeeSelector = '#adjustment_negative';

    /**
     * Update Totals button css selector.
     *
     * @var string
     */
    private $updateTotalsSelector = '.update-totals-button';

    /**
     * Submit invoice.
     *
     * @return void
     */
    public function submit()
    {
        $browser = $this->_rootElement;
        $selector = $this->submit . '.disabled';
        $strategy = Locator::SELECTOR_CSS;
        $browser->waitUntil(
            function () use ($browser, $selector, $strategy) {
                $element = $browser->find($selector, $strategy);
                return $element->isVisible() == false ? true : null;
            }
        );
        $this->_rootElement->find($this->submit)->click();
    }

    /**
     * Set Capture amount option:
     * Capture Online|Capture Offline|Not Capture
     *
     * @param string $option
     * @return void
     */
    public function setCaptureOption($option)
    {
        $this->_rootElement->find($this->capture, Locator::SELECTOR_CSS, 'select')->setValue($option);
    }

    /**
     * Get Refund Shipping input element.
     *
     * @return \Magento\Mtf\Client\ElementInterface
     */
    public function getRefundShippingElement()
    {
        return $this->_rootElement->find($this->refundShippingSelector, Locator::SELECTOR_CSS);
    }

    /**
     * Get Adjustment Refund input element.
     *
     * @return \Magento\Mtf\Client\ElementInterface
     */
    public function getAdjustmentRefundElement()
    {
        return $this->_rootElement->find($this->adjustmentRefundSelector, Locator::SELECTOR_CSS);
    }

    /**
     * Get Adjustment Fee input element.
     *
     * @return \Magento\Mtf\Client\ElementInterface
     */
    public function getAdjustmentFeeElement()
    {
        return $this->_rootElement->find($this->adjustmentFeeSelector, Locator::SELECTOR_CSS);
    }

    /**
     * Click update totals button.
     *
     * @return void
     */
    public function clickUpdateTotals()
    {
        $this->_rootElement->find($this->updateTotalsSelector)->click();
    }
}
