<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

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
    private $refundShippingSelector = '//input[@id=\'shipping_amount\']';

    /**
     * Adjustment Refund css selector.
     *
     * @var string
     */
    private $adjustmentRefundSelector = '//input[@id=\'adjustment_positive\']';

    /**
     * Adjustment Fee css selector.
     *
     * @var string
     */
    private $adjustmentFeeSelector = '//input[@id=\'adjustment_negative\']';

    /**
     * 'Update Totals button css selector.
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
        return $this->_rootElement->find($this->refundShippingSelector, Locator::SELECTOR_XPATH);
    }

    /**
     * Get Adjustment Refund input element.
     *
     * @return \Magento\Mtf\Client\ElementInterface
     */
    public function getAdjustmentRefundElement()
    {
        return $this->_rootElement->find($this->adjustmentRefundSelector, Locator::SELECTOR_XPATH);
    }

    /**
     * Get Adjustment Fee input element.
     *
     * @return \Magento\Mtf\Client\ElementInterface
     */
    public function getAdjustmentFeeElement()
    {
        return $this->_rootElement->find($this->adjustmentFeeSelector, Locator::SELECTOR_XPATH);
    }

    /**
     * Click update totals button.
     *
     * @return void
     */
    public function clickUpdateTotals()
    {
        $button = $this->_rootElement->find($this->updateTotalsSelector);
        if (!$button->isDisabled()) {
            $button->click();
        }
    }
}
