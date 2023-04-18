<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Block\Checkout\Shipping;

use Magento\Checkout\Block\Shipping\Price as ShippingPrice;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Tax\Helper\Data as TaxHelper;

/**
 * Class Price
 * @deprecated 100.1.0
 */
class Price extends ShippingPrice
{
    /**
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param CheckoutSession $checkoutSession
     * @param PriceCurrencyInterface $priceCurrency
     * @param TaxHelper $taxHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        PriceCurrencyInterface $priceCurrency,
        protected readonly TaxHelper $taxHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $checkoutSession,
            $priceCurrency,
            $data
        );
    }

    /**
     * Get Shipping Price including or excluding tax
     *
     * @param bool $flag
     * @return float
     */
    protected function getShippingPriceWithFlag($flag)
    {
        $price = $this->taxHelper->getShippingPrice(
            $this->getShippingRate()->getPrice(),
            $flag,
            $this->getAddress(),
            $this->getQuote()->getCustomerTaxClassId()
        );

        return $this->priceCurrency->convertAndFormat(
            $price,
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->getQuote()->getStore()
        );
    }

    /**
     * Get shipping price excluding tax
     *
     * @return float
     */
    public function getShippingPriceExclTax()
    {
        return $this->getShippingPriceWithFlag(false);
    }

    /**
     * Get shipping price including tax
     *
     * @return float
     */
    public function getShippingPriceInclTax()
    {
        return $this->getShippingPriceWithFlag(true);
    }

    /**
     * Return flag whether to display shipping price including tax
     *
     * @return bool
     */
    public function displayShippingPriceInclTax()
    {
        return $this->taxHelper->displayShippingPriceIncludingTax();
    }

    /**
     * Return flag whether to display shipping price excluding tax
     *
     * @return bool
     */
    public function displayShippingPriceExclTax()
    {
        return $this->taxHelper->displayShippingPriceExcludingTax();
    }

    /**
     * Return flag whether to display shipping price including and excluding tax
     *
     * @return bool
     */
    public function displayShippingBothPrices()
    {
        return $this->taxHelper->displayShippingBothPrices();
    }
}
