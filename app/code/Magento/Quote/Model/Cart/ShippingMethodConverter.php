<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Cart;

/**
 * Quote shipping method data.
 *
 */
class ShippingMethodConverter
{
    /**
     * Shipping method data factory.
     *
     * @var \Magento\Quote\Api\Data\ShippingMethodInterfaceFactory
     */
    protected $shippingMethodDataFactory;

    /**
     * Constructs a shipping method converter object.
     *
     * @param \Magento\Quote\Api\Data\ShippingMethodInterfaceFactory $shippingMethodDataFactory Shipping method factory.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager Store manager interface.
     */
    public function __construct(
        \Magento\Quote\Api\Data\ShippingMethodInterfaceFactory $shippingMethodDataFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->shippingMethodDataFactory = $shippingMethodDataFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Converts a specified rate model to a shipping method data object.
     *
     * @param string $quoteCurrencyCode The quote currency code.
     * @param \Magento\Quote\Model\Quote\Address\Rate $rateModel The rate model.
     * @return mixed Shipping method data object.
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
            ->setAvailable(empty($errorMessage));
    }
}
