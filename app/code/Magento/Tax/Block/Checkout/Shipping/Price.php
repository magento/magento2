<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Block\Checkout\Shipping;

use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class Price
 * @deprecated
 */
class Price extends \Magento\Checkout\Block\Shipping\Price
{
    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $taxHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Tax\Helper\Data $taxHelper,
        array $data = []
    ) {
        $this->taxHelper = $taxHelper;
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
