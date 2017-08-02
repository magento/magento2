<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\System\Config\Source\Coupon;

/**
 * Options for Code Format Field in Auto Generated Specific Coupon Codes configuration section
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Format implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Sales rule coupon
     *
     * @var \Magento\SalesRule\Helper\Coupon
     * @since 2.0.0
     */
    protected $_salesRuleCoupon = null;

    /**
     * @param \Magento\SalesRule\Helper\Coupon $salesRuleCoupon
     * @since 2.0.0
     */
    public function __construct(\Magento\SalesRule\Helper\Coupon $salesRuleCoupon)
    {
        $this->_salesRuleCoupon = $salesRuleCoupon;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
