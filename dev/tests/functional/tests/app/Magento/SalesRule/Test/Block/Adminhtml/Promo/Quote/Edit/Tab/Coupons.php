<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\Block\Adminhtml\Promo\Quote\Edit\Tab;

use Magento\Backend\Test\Block\Widget\Tab;
use Magento\Mtf\Client\Locator;
use Magento\SalesRule\Test\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons\Grid;

/**
 * Generate coupons tab.
 */
class Coupons extends Tab
{
    /**
     * Generate coupons button selector.
     *
     * @var string
     */
    protected $generateButton = '//button[contains(@onclick, "generateCouponCodes")]';

    /**
     * Generated coupons codes grid selector.
     *
     * @var string
     */
    protected $couponCodesGrid = '#couponCodesGrid';

    /**
     * Click generate coupon button.
     *
     * @return void
     */
    public function clickGenerate()
    {
        $this->_rootElement->find($this->generateButton, Locator::SELECTOR_XPATH)->click();
    }

    /**
     * Get generated coupons grid.
     *
     * @return Grid
     */
    public function getCouponsGrid()
    {
        return $this->blockFactory->create(
            Grid::class,
            ['element' => $this->_rootElement->find($this->couponCodesGrid)]
        );
    }
}
