<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Model\Adminhtml;

use Magento\Framework\Option\ArrayInterface;

/**
 * BillingAddressDisplayOptions gets list of configuration options for billing address displaying on
 * the Payment step on checkout
 */
class BillingAddressDisplayOptions implements ArrayInterface
{

    /**
     * Return array of options for billing address displaying on checkout payment step
     *
     * @return array:
     * [
     *  ['label' => 'Payment Method', 'value' => 0],
     *  ['label' => 'Payment Page', 'value' => 1]
     * ]
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => __('Payment Method'),
                'value' => 0
            ],
            [
                'label' => __('Payment Page'),
                'value' => 1
            ]
        ];
    }
}
