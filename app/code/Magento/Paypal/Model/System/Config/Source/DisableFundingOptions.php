<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Model\System\Config\Source;

/**
 * Get disable funding options
 */
class DisableFundingOptions
{
    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => \Magento\Paypal\Model\Express\Checkout::PAYPAL_FUNDING_CREDIT,
                'label' => __('PayPal Credit')
            ],
            [
                'value' => \Magento\Paypal\Model\Express\Checkout::PAYPAL_FUNDING_CARD,
                'label' => __('PayPal Guest Checkout Credit Card Icons')
            ],
            [
                'value' => \Magento\Paypal\Model\Express\Checkout::PAYPAL_FUNDING_ELV,
                'label' => __('Elektronisches Lastschriftverfahren - German ELV')
            ]
        ];
    }
}
