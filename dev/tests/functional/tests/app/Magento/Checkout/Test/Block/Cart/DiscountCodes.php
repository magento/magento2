<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Block\Cart;

use Mtf\Block\Form;
use Mtf\Client\Element\Locator;

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
     * Click apply button selector
     *
     * @var string
     */
    protected $applyButton = '.action.apply';

    /**
     * Enter discount code and click apply button
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
}
