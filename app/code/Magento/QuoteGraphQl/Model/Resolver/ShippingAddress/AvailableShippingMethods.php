<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver\ShippingAddress;

use Magento\Directory\Model\Currency;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Model\Cart\ShippingMethodConverter;
use Magento\Store\Api\Data\StoreInterface;

/**
 * @inheritdoc
 */
class AvailableShippingMethods implements ResolverInterface
{
    /**
     * @var ExtensibleDataObjectConverter
     */
    private $dataObjectConverter;

    /**
     * @var ShippingMethodConverter
     */
    private $shippingMethodConverter;

    /**
     * @param ExtensibleDataObjectConverter $dataObjectConverter
     * @param ShippingMethodConverter $shippingMethodConverter
     */
    public function __construct(
        ExtensibleDataObjectConverter $dataObjectConverter,
        ShippingMethodConverter $shippingMethodConverter
    ) {
        $this->dataObjectConverter = $dataObjectConverter;
        $this->shippingMethodConverter = $shippingMethodConverter;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" values should be specified'));
        }
        $address = $value['model'];

        // Allow shipping rates by setting country id for new addresses
        if (!$address->getCountryId() && $address->getCountryCode()) {
            $address->setCountryId($address->getCountryCode());
        }

        $address->setCollectShippingRates(true);
        $address->collectShippingRates();
        $cart = $address->getQuote();

        $methods = [];
        $shippingRates = $address->getGroupedAllShippingRates();
        foreach ($shippingRates as $carrierRates) {
            foreach ($carrierRates as $rate) {
                $methodData = $this->dataObjectConverter->toFlatArray(
                    $this->shippingMethodConverter->modelToDataObject($rate, $cart->getQuoteCurrencyCode()),
                    [],
                    ShippingMethodInterface::class
                );
                $methods[] = $this->processMoneyTypeData(
                    $methodData,
                    $cart->getQuoteCurrencyCode()
                );
            }
        }
        return $methods;
    }

    /**
     * Process money type data
     *
     * @param array $data
     * @param string $quoteCurrencyCode
     * @return array
     * @throws NoSuchEntityException
     */
    private function processMoneyTypeData(array $data, string $quoteCurrencyCode): array
    {
        if (isset($data['amount'])) {
            $data['amount'] = ['value' => $data['amount'], 'currency' => $quoteCurrencyCode];
        }

        /** @deprecated The field should not be used on the storefront */
        $data['base_amount'] = null;

        if (isset($data['price_excl_tax'])) {
            $data['price_excl_tax'] = ['value' => $data['price_excl_tax'], 'currency' => $quoteCurrencyCode];
        }

        if (isset($data['price_incl_tax'])) {
            $data['price_incl_tax'] = ['value' => $data['price_incl_tax'], 'currency' => $quoteCurrencyCode];
        }
        return $data;
    }
}
