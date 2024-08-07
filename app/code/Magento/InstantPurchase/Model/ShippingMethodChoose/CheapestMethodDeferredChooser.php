<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\Model\ShippingMethodChoose;

use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Rate;

/**
 * Choose cheapest shipping method for defined quote.
 */
class CheapestMethodDeferredChooser implements DeferredShippingMethodChooserInterface
{
    public const METHOD_CODE = 'cheapest';

    /**
     * @inheritdoc
     */
    public function choose(Address $address)
    {
        $shippingRates = $this->getShippingRates($address);
        if (empty($shippingRates)) {
            return null;
        }

        $cheapestRate = $this->selectCheapestRate($shippingRates);
        return $cheapestRate->getCode();
    }

    /**
     * Retrieves previously collected shipping rates or computes new ones.
     *
     * @param Address $address
     * @return Rate[]
     */
    private function getShippingRates(Address $address) : array
    {
        if (!empty($shippingRates = $address->getAllShippingRates())) {
            // Favour previously collected rates over recomputing.
            return $shippingRates;
        }
        $address->setCollectShippingRates(true);
        $address->collectShippingRates();
        return $address->getAllShippingRates();
    }

    /**
     * Selects shipping price with minimal price.
     *
     * @param Rate[] $shippingRates
     * @return Rate
     */
    private function selectCheapestRate(array $shippingRates) : Rate
    {
        $rate = array_shift($shippingRates);
        foreach ($shippingRates as $tmpRate) {
            if ($tmpRate->getPrice() < $rate->getPrice()) {
                $rate = $tmpRate;
            }
        }
        return $rate;
    }
}
