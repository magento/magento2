<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\Model\QuoteManagement;

use Magento\Framework\Exception\LocalizedException;
use Magento\InstantPurchase\Model\ShippingMethodChoose\DeferredShippingMethodChooserInterface;
use Magento\InstantPurchase\Model\ShippingMethodChoose\DeferredShippingMethodChooserPool;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;

/**
 * Configure shipping method for instant purchase
 *
 * @api May be used for pluginization.
 * @since 100.2.0
 */
class ShippingConfiguration
{
    /**
     * @var DeferredShippingMethodChooserPool
     */
    private $deferredShippingMethodChooserPool;

    /**
     * ShippingConfiguration constructor.
     * @param DeferredShippingMethodChooserPool $deferredShippingMethodChooserPool
     */
    public function __construct(
        DeferredShippingMethodChooserPool $deferredShippingMethodChooserPool
    ) {
        $this->deferredShippingMethodChooserPool = $deferredShippingMethodChooserPool;
    }

    /**
     * Sets shipping information to quote.
     *
     * @param Quote $quote
     * @param ShippingMethodInterface $shippingMethod
     * @return Quote
     * @throws LocalizedException if shipping can not be configured for a quote.
     * @since 100.2.0
     */
    public function configureShippingMethod(
        Quote $quote,
        ShippingMethodInterface $shippingMethod
    ): Quote {
        if ($quote->isVirtual()) {
            return $quote;
        }

        $shippingAddress = $quote->getShippingAddress();
        $shippingMethodCode = $this->getShippingMethodCodeToUse($shippingAddress, $shippingMethod);
        $shippingAddress->setShippingMethod($shippingMethodCode);

        return $quote;
    }

    /**
     * Build quote specific shipping method code based on shipping method.
     *
     * @param Address $address
     * @param ShippingMethodInterface $shippingMethod
     * @return string
     * @throws LocalizedException if shipping code can not be detected.
     */
    private function getShippingMethodCodeToUse(
        Address $address,
        ShippingMethodInterface $shippingMethod
    ): string {
        if ($shippingMethod->getCarrierCode() === DeferredShippingMethodChooserInterface::CARRIER) {
            return $this->resolveDeferredShippingMethodChoose($address, $shippingMethod);
        } else {
            return $this->getCorrespondingShippingRateCode($address, $shippingMethod);
        }
    }

    /**
     * Detects real shipping method code.
     *
     * @param Address $address
     * @param ShippingMethodInterface $shippingMethod
     * @return string
     * @throws LocalizedException if shipping method not applicable to quote.
     */
    private function getCorrespondingShippingRateCode(
        Address $address,
        ShippingMethodInterface $shippingMethod
    ): string {
        $address->setCollectShippingRates(true);
        $address->collectShippingRates();
        $shippingRates = $address->getAllShippingRates();
        foreach ($shippingRates as $shippingRate) {
            if ($shippingRate->getCarrier() === $shippingMethod->getCarrierCode()
                &&
                $shippingRate->getMethod() === $shippingMethod->getMethodCode()
            ) {
                return $shippingRate->getCode();
            }
        }
        throw new LocalizedException(__('Specified shipping method is not available.'));
    }

    /**
     * Detect real shipping method based on provided strategy and shipping address with quote data.
     *
     * @param Address $address
     * @param ShippingMethodInterface $shippingMethod
     * @return string
     * @throws LocalizedException if appropriate shipping method is not available
     */
    private function resolveDeferredShippingMethodChoose(
        Address $address,
        ShippingMethodInterface $shippingMethod
    ): string {
        $deferredShippingMethodChooser = $this->deferredShippingMethodChooserPool->get(
            $shippingMethod->getMethodCode()
        );

        $shippingMethodCode = $deferredShippingMethodChooser->choose($address);
        if (empty($shippingMethodCode)) {
            throw new LocalizedException(__('Appropriate shipping method is not available.'));
        }

        return $shippingMethodCode;
    }
}
