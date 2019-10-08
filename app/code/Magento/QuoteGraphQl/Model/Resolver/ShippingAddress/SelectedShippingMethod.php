<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver\ShippingAddress;

use Magento\Directory\Model\Currency;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Rate;

/**
 * @inheritdoc
 */
class SelectedShippingMethod implements ResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        /** @var Address $address */
        $address = $value['model'];
        $rates = $address->getAllShippingRates();
        $carrierTitle = null;
        $methodTitle = null;

        if (count($rates) > 0 && !empty($address->getShippingMethod())) {
            list($carrierCode, $methodCode) = explode('_', $address->getShippingMethod(), 2);

            /** @var Rate $rate */
            foreach ($rates as $rate) {
                if ($rate->getCode() == $address->getShippingMethod()) {
                    $carrierTitle = $rate->getCarrierTitle();
                    $methodTitle = $rate->getMethodTitle();
                    break;
                }
            }

            /** @var Currency $currency */
            $currency = $context->getExtensionAttributes()->getStore()->getBaseCurrency();

            $data = [
                'carrier_code' => $carrierCode,
                'method_code' => $methodCode,
                'carrier_title' => $carrierTitle,
                'method_title' => $methodTitle,
                'amount' => [
                    'value' => $address->getShippingAmount(),
                    'currency' => $address->getQuote()->getQuoteCurrencyCode(),
                ],
                'base_amount' => [
                    'value' => $address->getBaseShippingAmount(),
                    'currency' => $currency->getCode(),
                ],
            ];
        } else {
            $data = [
                'carrier_code' => null,
                'method_code' => null,
                'carrier_title' => $carrierTitle,
                'method_title' => $methodTitle,
                'amount' => null,
                'base_amount' => null,
            ];
        }
        return $data;
    }
}
