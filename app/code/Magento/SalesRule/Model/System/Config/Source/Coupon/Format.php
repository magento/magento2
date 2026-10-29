<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\SalesRule\Model\System\Config\Source\Coupon;

/**
 * Options for Code Format Field in Auto Generated Specific Coupon Codes configuration section
 */
class Format implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\SalesRule\Helper\Coupon
     */
    protected $_salesRuleCoupon = null;

    /**
     * @param \Magento\SalesRule\Helper\Coupon $salesRuleCoupon
     */
    public function __construct(\Magento\SalesRule\Helper\Coupon $salesRuleCoupon)
    {
        $this->_salesRuleCoupon = $salesRuleCoupon;
    }

    /**
     * @inheritdoc
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
