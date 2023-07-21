<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver\ShippingAddress;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\Cart\ShippingMethodConverter;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Rate;

/**
 * @inheritdoc
 */
class SelectedShippingMethod implements ResolverInterface
{
    /**
     * @var ShippingMethodConverter
     */
    private $shippingMethodConverter;

    /**
     * @param ShippingMethodConverter $shippingMethodConverter
     */
    public function __construct(ShippingMethodConverter $shippingMethodConverter)
    {
        $this->shippingMethodConverter = $shippingMethodConverter;
    }

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

        if (!count($rates) || empty($address->getShippingMethod())) {
            return null;
        }

        /** @var Rate $rate */
        $carrierCode = $methodCode = null;
        foreach ($rates as $rate) {
            if ($rate->getCode() === $address->getShippingMethod()) {
                $carrierCode = $rate->getCarrier();
                $methodCode = $rate->getMethod();
                break;
            }
        }

        $cart = $address->getQuote();
        $selectedShippingMethod = $this->shippingMethodConverter->modelToDataObject(
            $rate,
            $cart->getQuoteCurrencyCode()
        );

        return [
            'carrier_code' => $carrierCode,
            'method_code' => $methodCode,
            'carrier_title' => $selectedShippingMethod->getCarrierTitle() ?? '',
            'method_title' => $selectedShippingMethod->getMethodTitle() ?? '',
            'amount' => [
                'value' => $address->getShippingAmount(),
                'currency' => $cart->getQuoteCurrencyCode(),
            ],
            'price_excl_tax' => [
                'value' => $selectedShippingMethod->getPriceExclTax(),
                'currency' => $cart->getQuoteCurrencyCode(),
            ],
            'price_incl_tax' => [
                'value' => $selectedShippingMethod->getPriceInclTax(),
                'currency' => $cart->getQuoteCurrencyCode(),
            ],
            /** @deprecated The field should not be used on the storefront */
            'base_amount' => null,
        ];
    }
}
