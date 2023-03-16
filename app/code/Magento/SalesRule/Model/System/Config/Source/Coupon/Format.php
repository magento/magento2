<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\System\Config\Source\Coupon;

use Magento\Framework\Option\ArrayInterface;
use Magento\SalesRule\Helper\Coupon as CouponHelper;

/**
 * Options for Code Format Field in Auto Generated Specific Coupon Codes configuration section
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Format implements ArrayInterface
{
    /**
     * Sales rule coupon
     *
     * @var CouponHelper
     */
    protected $_salesRuleCoupon = null;

    /**
     * @param CouponHelper $salesRuleCoupon
     */
    public function __construct(
        CouponHelper $salesRuleCoupon
    ) {
        $this->_salesRuleCoupon = $salesRuleCoupon;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $formatsList = $this->_salesRuleCoupon->getFormatsList();
        $result = [];
        foreach ($formatsList as $formatId => $formatTitle) {
            $result[] = ['value' => $formatId, 'label' => $formatTitle];
        }

        return $result;
    }
}
