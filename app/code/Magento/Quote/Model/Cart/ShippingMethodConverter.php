<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Cart;

/**
 * Quote shipping method data.
 *
 * @since 2.0.0
 */
class ShippingMethodConverter
{
    /**
     * Shipping method data factory.
     *
     * @var \Magento\Quote\Api\Data\ShippingMethodInterfaceFactory
     * @since 2.0.0
     */
    protected $shippingMethodDataFactory;

    /**
     * @var \Magento\Tax\Helper\Data
     * @since 2.0.0
     */
    protected $taxHelper;

    /**
     * Constructs a shipping method converter object.
     *
     * @param \Magento\Quote\Api\Data\ShippingMethodInterfaceFactory $shippingMethodDataFactory Shipping method factory.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager Store manager interface.
     * @param \Magento\Tax\Helper\Data $taxHelper Tax data helper.
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Quote\Api\Data\ShippingMethodInterfaceFactory $shippingMethodDataFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Tax\Helper\Data $taxHelper
    ) {
        $this->shippingMethodDataFactory = $shippingMethodDataFactory;
        $this->storeManager = $storeManager;
        $this->taxHelper = $taxHelper;
    }

    /**
     * Converts a specified rate model to a shipping method data object.
     *
     * @param string $quoteCurrencyCode The quote currency code.
     * @param \Magento\Quote\Model\Quote\Address\Rate $rateModel The rate model.
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface Shipping method data object.
     * @since 2.0.0
     */
    public function modelToDataObject($rateModel, $quoteCurrencyCode)
    {
        /** @var \Magento\Directory\Model\Currency $currency */
        $currency = $this->storeManager->getStore()->getBaseCurrency();

        $errorMessage = $rateModel->getErrorMessage();
        return $this->shippingMethodDataFactory->create()
            ->setCarrierCode($rateModel->getCarrier())
            ->setMethodCode($rateModel->getMethod())
            ->setCarrierTitle($rateModel->getCarrierTitle())
            ->setMethodTitle($rateModel->getMethodTitle())
            ->setAmount($currency->convert($rateModel->getPrice(), $quoteCurrencyCode))
            ->setBaseAmount($rateModel->getPrice())
            ->setAvailable(empty($errorMessage))
            ->setErrorMessage(empty($errorMessage) ? false : $errorMessage)
            ->setPriceExclTax(
                $currency->convert($this->getShippingPriceWithFlag($rateModel, false), $quoteCurrencyCode)
            )
            ->setPriceInclTax(
                $currency->convert($this->getShippingPriceWithFlag($rateModel, true), $quoteCurrencyCode)
            );
    }

    /**
     * Get Shipping Price including or excluding tax
     *
     * @param \Magento\Quote\Model\Quote\Address\Rate $rateModel
     * @param bool $flag
     * @return float
     * @since 2.0.0
     */
    private function getShippingPriceWithFlag($rateModel, $flag)
    {
        return $this->taxHelper->getShippingPrice(
            $rateModel->getPrice(),
            $flag,
            $rateModel->getAddress(),
            $rateModel->getAddress()->getQuote()->getCustomerTaxClassId()
        );
    }
}
