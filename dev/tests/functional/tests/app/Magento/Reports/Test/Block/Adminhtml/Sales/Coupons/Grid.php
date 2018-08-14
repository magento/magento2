<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Block\Adminhtml\Sales\Coupons;

/**
 * Class Grid
 * Coupons report grid
 */
class Grid extends \Magento\Backend\Test\Block\Widget\Grid
{
    /**
     * Filters array mapping
     *
     * @var array
     */
    protected $filters = [
        'coupon_code' => [
            'selector' => '.col-coupon_code',
        ],
        'rule_name' => [
            'selector' => '.col-rule_name',
        ],
        'subtotal' => [
            'selector' => '.col-subtotal',
        ],
        'discount' => [
            'selector' => '.col-discount',
        ],
    ];
}
