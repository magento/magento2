<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\Block\Adminhtml\Promo\Quote\Edit\Section;

use Magento\Mtf\Client\Locator;
use Magento\SalesRule\Test\Block\Adminhtml\Promo\Quote\Edit\Section\BlockPromoSalesRuleEditTabCoupons\Grid;
use Magento\Ui\Test\Block\Adminhtml\Section;

/**
 * Sales rule BlockPromoSalesRuleEditTabCoupons section.
 */
class BlockPromoSalesRuleEditTabCoupons extends Section
{
    /**
     * Success message selector.
     *
     * @var string
     */
    protected $successMessage = '[data-ui-id$=message-success]';

    /**
     * Generate button which generates coupons.
     *
     * @var string
     */
    private $generateButtonSelector = './/*[@id="coupons_generate_button"]//button[contains(@class, "generate")]';

    /**
     * Coupon codes grid.
     *
     * @var string
     */
    private $gridSelector = '#couponCodesGrid';

    /**
     * Press generate button to generate coupons.
     *
     * @return void
     */
    public function pressGenerateButton()
    {
        $this->_rootElement->find($this->generateButtonSelector, Locator::SELECTOR_XPATH)->click();

        $this->waitForElementVisible($this->successMessage);
    }

    /**
     * Get success message from section.
     *
     * @return string
     */
    public function getSuccessMessage()
    {
        $this->waitForElementVisible($this->successMessage);

        return $this->_rootElement->find($this->successMessage)->getText();
    }

    /**
     * Get coupon codes grid.
     *
     * @return Grid
     */
    public function getCouponGrid()
    {
        $element = $this->_rootElement->find($this->gridSelector);

        /** @var Grid $couponGrid */
        $couponGrid = $this->blockFactory->create(Grid::class, ['element' => $element]);

        return $couponGrid;
    }
}
