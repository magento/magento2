<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create\Coupons;

/**
 * Adminhtml sales order create coupons form block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Form extends \Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate
{
    /**
     * Constructor
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('sales_order_create_coupons_form');
    }

    /**
     * Get coupon code
     *
     * @return string
     * @since 2.0.0
     */
    public function getCouponCode()
    {
        return $this->getParentBlock()->getQuote()->getCouponCode();
    }
}
