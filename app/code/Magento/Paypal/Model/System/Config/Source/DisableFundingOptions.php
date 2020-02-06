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
                'value' => 'CREDIT',
                'label' => __('PayPal Credit')
            ],
            [
                'value' => 'CARD',
                'label' => __('PayPal Guest Checkout Credit Card Icons')
            ],
            [
                'value' => 'ELV',
                'label' => __('Elektronisches Lastschriftverfahren - German ELV')
            ]
        ];
    }
}
