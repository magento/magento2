<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;

class CheapestShippingRateChooser implements ShippingRateChooserInterface
{
    /**
     * @param Quote $quote
     * @return Quote
     * @throws LocalizedException
     */
    public function choose(Quote $quote): Quote
    {
        if ($quote->isVirtual()) {
            return $quote;
        }

        $address = $quote->getShippingAddress();

        $shippingRates = $address
            ->setCollectShippingRates(true)
            ->collectShippingRates()
            ->getAllShippingRates();
        if (empty($shippingRates)) {
            throw new LocalizedException(
                __('There are no shipping methods available for default shipping address.')
            );
        }

        $rate = array_shift($shippingRates);

        foreach ($shippingRates as $tmpRate) {
            if ($tmpRate['price'] < $rate['price']) {
                $rate = $tmpRate;
            }
        }
        $address->setShippingMethod($rate['code']);

        return $quote;
    }
}
