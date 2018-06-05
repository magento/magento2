<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Block\Cart;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;

/**
 * Class DiscountCodes
 * Discount codes block
 */
class DiscountCodes extends Form
{
    /**
     * Form wrapper selector
     *
     * @var string
     */
    protected $formWrapper = '.content';

    /**
     * Open discount codes form selector
     *
     * @var string
     */
    protected $openForm = '.title';

    /**
     * Fill discount code input selector
     *
     * @var string
     */
    protected $couponCode = '#coupon_code';

    /**
     * Locator for "Apply Discount" button.
     *
     * @var string
     */
    protected $applyButton = '.action.apply';

    /**
     * Locator for "Cancel Coupon" button.
     *
     * @var string
     */
    protected $cancelButton = '.action.cancel';

    /**
     * Enter Discount Code and click "Apply Discount" button.
     *
     * @param string $code
     * @return void
     */
    public function applyCouponCode($code)
    {
        if (!$this->_rootElement->find($this->formWrapper)->isVisible()) {
            $this->_rootElement->find($this->openForm, Locator::SELECTOR_CSS)->click();
        }
        $this->_rootElement->find($this->couponCode, Locator::SELECTOR_CSS)->setValue($code);
        $this->_rootElement->find($this->applyButton, Locator::SELECTOR_CSS)->click();
    }

    /**
     * Click "Cancel Coupon" button.
     *
     * @return void
     */
    public function cancelCouponCode()
    {
        if (!$this->_rootElement->find($this->formWrapper)->isVisible()) {
            $this->_rootElement->find($this->openForm)->click();
        }
        $this->_rootElement->find($this->cancelButton)->click();
    }
}
