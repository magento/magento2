<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Ui\Component\Report\Listing\Column;

use Braintree\PaymentInstrumentType;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class PaymentType
 */
class PaymentType implements OptionSourceInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $types = $this->getAvailablePaymentTypes();
        foreach ($types as $typeCode => $typeName) {
            $this->options[$typeCode]['label'] = $typeName;
            $this->options[$typeCode]['value'] = $typeCode;
        }

        return $this->options;
    }

    /**
     * @return array
     */
    private function getAvailablePaymentTypes()
    {
        return [
            PaymentInstrumentType::PAYPAL_ACCOUNT => __('Paypal account'),
            PaymentInstrumentType::COINBASE_ACCOUNT => __('Coinbase account'),
            PaymentInstrumentType::EUROPE_BANK_ACCOUNT => __('Europe bank account'),
            PaymentInstrumentType::CREDIT_CARD => __('Credit card'),
            PaymentInstrumentType::APPLE_PAY_CARD => __('Apple pay card'),
            PaymentInstrumentType::ANDROID_PAY_CARD => __('Android pay card')
        ];
    }
}
