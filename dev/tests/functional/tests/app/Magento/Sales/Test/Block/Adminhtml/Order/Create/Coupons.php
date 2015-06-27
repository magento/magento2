<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Create;

use Magento\Mtf\Block\Form;
use Magento\SalesRule\Test\Fixture\SalesRule;
use Magento\Mtf\Client\Locator;

/**
 * Adminhtml sales order create coupons block.
 */
class Coupons extends Form
{
    /**
     * Fill discount code input selector.
     *
     * @var string
     */
    protected $couponCode = 'input[name="coupon_code"]';

    /**
     * Click apply button selector.
     *
     * @var string
     */
    protected $applyButton = '//*[@id="coupons:code"]/following-sibling::button';

    /**
     * Enter discount code and click apply button.
     *
     * @param SalesRule $code
     * @return void
     */
    public function applyCouponCode(SalesRule $code)
    {
        $this->_rootElement->find($this->couponCode)->setValue($code->getCouponCode());
        $this->_rootElement->find($this->applyButton, Locator::SELECTOR_XPATH)->click();
    }
}
