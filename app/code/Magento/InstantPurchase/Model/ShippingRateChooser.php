<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;

class ShippingRateChooser
{
    /**
     * @var ShippingRateChooserRuleInterface
     */
    private $shippingRateChooserRule;

    /**
     * ShippingRateChooser constructor.
     * @param ShippingRateChooserRuleInterface $shippingRateChooserRule
     */
    public function __construct(
        ShippingRateChooserRuleInterface $shippingRateChooserRule
    ) {
        $this->shippingRateChooserRule = $shippingRateChooserRule;
    }

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
                __('There are no shipping methods available.')
            );
        }

        $shippingRate = $this->shippingRateChooserRule->choose($shippingRates);
        $address->setShippingMethod($shippingRate);

        return $quote;
    }
}
