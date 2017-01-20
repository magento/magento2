<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Block\Onepage\Payment;

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
    protected $openForm = '.payment-option-title';

    /**
     * Fill discount code input selector
     *
     * @var string
     */
    protected $couponCode = '#discount-code';

    /**
     * Click apply button selector
     *
     * @var string
     */
    protected $applyButton = '.action.action-apply';

    /**
     * Enter discount code and click apply button
     *
     * @param string $code
     * @return void
     */
    public function applyCouponCode($code)
    {
        $this->_rootElement->find($this->openForm, Locator::SELECTOR_CSS)->click();
        $this->_rootElement->find($this->couponCode, Locator::SELECTOR_CSS)->setValue($code);
        $this->_rootElement->find($this->applyButton, Locator::SELECTOR_CSS)->click();
    }
}
