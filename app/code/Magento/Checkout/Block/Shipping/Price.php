<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Shipping;

use Magento\Checkout\Block\Cart\AbstractCart;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Address\Rate;

/**
 * Class Price
 * @deprecated
 */
class Price extends AbstractCart
{
    /**
     * @var Rate
     */
    protected $shippingRate;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context, $customerSession, $checkoutSession, $data);
    }

    /**
     * Set the shipping rate
     *
     * @param Rate $shippingRate
     * @return $this
     */
    public function setShippingRate(Rate $shippingRate)
    {
        $this->shippingRate = $shippingRate;
        return $this;
    }

    /**
     * Return shipping rate
     *
     * @return Rate
     */
    public function getShippingRate()
    {
        return $this->shippingRate;
    }

    /**
     * Get Shipping Price
     *
     * @return float
     */
    public function getShippingPrice()
    {
        return $this->priceCurrency->convertAndFormat($this->shippingRate->getPrice());
    }
}
