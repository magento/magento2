<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Model\System\Config\Source\ExpressButtons;

class DisableFundingOptions
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'paypal.FUNDING.CREDIT', 'label' => 'PayPal Credit'],
            ['value' => 'paypal.FUNDING.CARD', 'label' => 'PayPal Guest Checkout Credit Card Icons'],
            ['value' => 'paypal.FUNDING.ELV', 'label' => 'Elektronisches Lastschriftverfahren - German ELV']
        ];
    }
}
