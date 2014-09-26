<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Tax\Block\Checkout\Shipping;

use Magento\Framework\Pricing\PriceCurrencyInterface;

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
        array $data = array()
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
