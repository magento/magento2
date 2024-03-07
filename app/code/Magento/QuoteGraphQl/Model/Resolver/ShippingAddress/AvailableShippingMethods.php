<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver\ShippingAddress;

use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Model\Cart\ShippingMethodConverter;
use Magento\QuoteGraphQl\Model\Cart\TotalsCollector;
use Magento\QuoteGraphQl\Model\FormatMoneyTypeData;

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
     * @var FormatMoneyTypeData
     */
    private FormatMoneyTypeData $formatMoneyTypeData;

    /**
     * @var TotalsCollector
     */
    private $totalsCollector;

    /**
     * @param ExtensibleDataObjectConverter $dataObjectConverter
     * @param ShippingMethodConverter $shippingMethodConverter
     * @param FormatMoneyTypeData $formatMoneyTypeData
     * @param TotalsCollector $totalsCollector
     */
    public function __construct(
        ExtensibleDataObjectConverter $dataObjectConverter,
        ShippingMethodConverter $shippingMethodConverter,
        FormatMoneyTypeData $formatMoneyTypeData,
        TotalsCollector $totalsCollector,
    ) {
        $this->dataObjectConverter = $dataObjectConverter;
        $this->shippingMethodConverter = $shippingMethodConverter;
        $this->formatMoneyTypeData = $formatMoneyTypeData;
        $this->totalsCollector = $totalsCollector;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" values should be specified'));
        }
        $address = clone $value['model'];
        $address->setLimitCarrier(null);

        // Allow shipping rates by setting country id for new addresses
        if (!$address->getCountryId() && $address->getCountryCode()) {
            $address->setCountryId($address->getCountryCode());
        }

        $address->setCollectShippingRates(true);
        $cart = $address->getQuote();
        $this->totalsCollector->collectAddressTotals($cart, $address);
        $methods = [];
        $shippingRates = $address->getGroupedAllShippingRates();
        foreach ($shippingRates as $carrierRates) {
            foreach ($carrierRates as $rate) {
                $methodData = $this->dataObjectConverter->toFlatArray(
                    $this->shippingMethodConverter->modelToDataObject($rate, $cart->getQuoteCurrencyCode()),
                    [],
                    ShippingMethodInterface::class
                );
                $methods[] = $this->formatMoneyTypeData->execute(
                    $methodData,
                    $cart->getQuoteCurrencyCode()
                );
            }
        }
        return $methods;
    }
}
