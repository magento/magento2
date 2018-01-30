<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
            PaymentInstrumentType::PAYPAL_ACCOUNT => __(PaymentInstrumentType::PAYPAL_ACCOUNT),
            PaymentInstrumentType::COINBASE_ACCOUNT => __(PaymentInstrumentType::COINBASE_ACCOUNT),
            PaymentInstrumentType::EUROPE_BANK_ACCOUNT => __(PaymentInstrumentType::EUROPE_BANK_ACCOUNT),
            PaymentInstrumentType::CREDIT_CARD => __(PaymentInstrumentType::CREDIT_CARD),
            PaymentInstrumentType::APPLE_PAY_CARD => __(PaymentInstrumentType::APPLE_PAY_CARD),
            PaymentInstrumentType::ANDROID_PAY_CARD => __(PaymentInstrumentType::ANDROID_PAY_CARD)
        ];
    }
}
