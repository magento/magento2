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
use Magento\NegotiableQuote\Model\PriceCurrency;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Rate;

/**
 * @inheritdoc
 */
class SelectedShippingMethod implements ResolverInterface
{
    /**
     * @var PriceCurrency
     */
    private $priceCurrency;

    /**
     * Discounts constructor.
     * @param PriceCurrency $priceCurrency
     */
    public function __construct(PriceCurrency $priceCurrency){
        $this->priceCurrency = $priceCurrency;
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
        $carrierTitle = '';
        $methodTitle = '';

        if (!count($rates) || empty($address->getShippingMethod())) {
            return null;
        }

        list($carrierCode, $methodCode) = explode('_', $address->getShippingMethod(), 2);

        /** @var Rate $rate */
        foreach ($rates as $rate) {
            if ($rate->getCode() == $address->getShippingMethod()) {
                $carrierTitle = $rate->getCarrierTitle();
                $methodTitle = $rate->getMethodTitle();
                break;
            }
        }

        $data = [
            'carrier_code' => $carrierCode,
            'method_code' => $methodCode,
            'carrier_title' => $carrierTitle,
            'method_title' => $methodTitle,
            'amount' => [
                'value' => $address->getShippingAmount(),
                'currency' => $address->getQuote()->getQuoteCurrencyCode(),
                'formatted' => $this->priceCurrency->format($address->getShippingAmount(),false,null,null,$address->getQuote()->getQuoteCurrencyCode()),
            ],
            /** @deprecated The field should not be used on the storefront */
            'base_amount' => null,
        ];

        return $data;
    }
}
