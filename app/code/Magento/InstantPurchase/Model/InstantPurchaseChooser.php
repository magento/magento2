<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\Model;

use Magento\Customer\Model\Customer;

use Magento\InstantPurchase\Model\PaymentMethodChoose\PaymentTokenChooserInterface;
use Magento\InstantPurchase\Model\ShippingAddressChoose\ShippingAddressChooserInterface;
use Magento\InstantPurchase\Model\BillingAddressChoose\BillingAddressChooserInterface;
use Magento\InstantPurchase\Model\ShippingMethodChoose\ShippingMethodChooserInterface;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Choose instant purchase option programmatically based on configured implementation.
 *
 * Provide implementations of injected chooser interfaces to customize behavior.
 */
class InstantPurchaseChooser implements InstantPurchaseInterface
{
    /**
     * @var InstantPurchaseOptionFactory
     */
    private $instantPurchaseOptionFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var PaymentTokenChooserInterface
     */
    private $paymentTokenChooser;

    /**
     * @var ShippingAddressChooserInterface
     */
    private $shippingAddressChooser;

    /**
     * @var BillingAddressChooserInterface
     */
    private $billingAddressChooser;

    /**
     * @var ShippingMethodChooserInterface
     */
    private $shippingMethodChooser;

    /**
     * InstantPurchase constructor.
     * @param InstantPurchaseOptionFactory $instantPurchaseOptionFactory
     * @param Config $config
     * @param PaymentTokenChooserInterface $paymentTokenChooser
     * @param ShippingAddressChooserInterface $shippingAddressChooser
     * @param BillingAddressChooserInterface $billingAddressChooser
     * @param ShippingMethodChooserInterface $shippingMethodChooser
     */
    public function __construct(
        InstantPurchaseOptionFactory $instantPurchaseOptionFactory,
        Config $config,
        PaymentTokenChooserInterface $paymentTokenChooser,
        ShippingAddressChooserInterface $shippingAddressChooser,
        BillingAddressChooserInterface $billingAddressChooser,
        ShippingMethodChooserInterface $shippingMethodChooser
    ) {
        $this->instantPurchaseOptionFactory = $instantPurchaseOptionFactory;
        $this->config = $config;
        $this->paymentTokenChooser = $paymentTokenChooser;
        $this->shippingAddressChooser = $shippingAddressChooser;
        $this->billingAddressChooser = $billingAddressChooser;
        $this->shippingMethodChooser = $shippingMethodChooser;
    }

    /**
     * @inheritdoc
     */
    public function getOption(
        StoreInterface $store,
        Customer $customer
    ): InstantPurchaseOption {
        if (!$this->isInstantPurchaseButtonEnabled($store)) {
            return $this->instantPurchaseOptionFactory->createDisabledOption();
        }

        $paymentToken = $this->paymentTokenChooser->choose($store, $customer);
        $shippingAddress = $this->shippingAddressChooser->choose($customer);
        $billingAddress = $this->billingAddressChooser->choose($customer);
        if ($shippingAddress) {
            $shippingMethod = $this->shippingMethodChooser->choose($shippingAddress);
        } else {
            $shippingMethod = null;
        }

        $option = $this->instantPurchaseOptionFactory->create(
            $paymentToken,
            $shippingAddress,
            $billingAddress,
            $shippingMethod
        );

        return $option;
    }

    /**
     * Checks if button available.
     *
     * @param StoreInterface $store
     * @return bool
     */
    private function isInstantPurchaseButtonEnabled(StoreInterface $store): bool
    {
        return $this->config->isModuleEnabled($store->getId());
    }
}
