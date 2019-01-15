<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Block\Adminhtml\Promo\Quote\Edit\Section;

use Magento\Mtf\Client\Locator;
use Magento\Ui\Test\Block\Adminhtml\Section;

/**
 * Backend sales rule label section.
 */
class ManageCouponCode extends Section
{
    const GENERATE_CODES_BUTTON_CSS_SELECTOR = ".action-default.scalable.generate";

    const LAST_GENERATED_COUPON_CODE_SELECTOR = "//*[@id=\"couponCodesGrid_table\"]/tbody/tr/td[2]";

    const SPINNER = ".loading-mask";

    /**
     * Click on generate button in order to generate coupon codes
     *
     * @return void
     */
    public function generateCouponCodes()
    {
        $button = $this->_rootElement->find(self::GENERATE_CODES_BUTTON_CSS_SELECTOR);
        $button->click();
    }

    /**
     * Retrieve last generated coupon code
     *
     * @return string
     */
    public function getGeneratedCouponCode()
    {
        $this->waitForSpinner();
        $column = $this->_rootElement->find(self::LAST_GENERATED_COUPON_CODE_SELECTOR, Locator::SELECTOR_XPATH);
        return $column->getText();
    }

    private function waitForSpinner()
    {
        $this->waitForElementNotVisible(self::SPINNER);
        sleep(1);
    }
}
