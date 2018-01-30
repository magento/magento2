<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Invoice;

use Magento\Mtf\Client\Locator;

/**
 * Class Totals
 * Invoice totals block
 */
class Totals extends \Magento\Sales\Test\Block\Adminhtml\Order\Totals
{
    /**
     * Submit invoice button selector
     *
     * @var string
     */
    protected $submit = '[data-ui-id="order-items-submit-button"]';

    /**
     * Capture amount select selector
     *
     * @var string
     */
    protected $capture = '[name="invoice[capture_case]"]';

    /**
     * Submit invoice
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
}
