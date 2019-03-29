<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CheckoutAgreements\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class AgreementForms implements OptionSourceInterface
{
    const CHECKOUT_CODE = 'checkout';
    const CUSTOMER_REGISTRATION_CODE = 'customer_account_create';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::CHECKOUT_CODE, 'label' => __('Checkout')],
            ['value' => self::CUSTOMER_REGISTRATION_CODE, 'label' => __('Customer Registration')],
        ];
    }
}
