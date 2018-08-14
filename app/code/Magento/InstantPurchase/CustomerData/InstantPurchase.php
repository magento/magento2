<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Model\Session;
use Magento\InstantPurchase\Model\InstantPurchaseInterface as InstantPurchaseModel;
use Magento\InstantPurchase\Model\Ui\CustomerAddressesFormatter;
use Magento\InstantPurchase\Model\Ui\PaymentTokenFormatter;
use Magento\InstantPurchase\Model\Ui\ShippingMethodFormatter;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Instant Purchase private customer data source.
 *
 * Contains all required data to perform instance purchase:
 *  - payment method
 *  - shipping address
 *  - billing address
 *  - shipping method
 */
class InstantPurchase implements SectionSourceInterface
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var InstantPurchaseModel
     */
    private $instantPurchase;

    /**
     * @var PaymentTokenFormatter
     */
    private $paymentTokenFormatter;

    /**
     * @var CustomerAddressesFormatter
     */
    private $customerAddressesFormatter;

    /**
     * @var ShippingMethodFormatter
     */
    private $shippingMethodFormatter;

    /**
     * InstantPurchase constructor.
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param InstantPurchaseModel $instantPurchase
     * @param PaymentTokenFormatter $paymentTokenFormatter
     * @param CustomerAddressesFormatter $customerAddressesFormatter
     * @param ShippingMethodFormatter $shippingMethodFormatter
     */
    public function __construct(
        Session $customerSession,
        StoreManagerInterface $storeManager,
        InstantPurchaseModel $instantPurchase,
        PaymentTokenFormatter $paymentTokenFormatter,
        CustomerAddressesFormatter $customerAddressesFormatter,
        ShippingMethodFormatter $shippingMethodFormatter
    ) {
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->instantPurchase = $instantPurchase;
        $this->paymentTokenFormatter = $paymentTokenFormatter;
        $this->customerAddressesFormatter = $customerAddressesFormatter;
        $this->shippingMethodFormatter = $shippingMethodFormatter;
    }

    /**
     * @inheritdoc
     */
    public function getSectionData(): array
    {
        if (!$this->customerSession->isLoggedIn()) {
            return ['available' => false];
        }

        $store = $this->storeManager->getStore();
        $customer = $this->customerSession->getCustomer();
        $instantPurchaseOption = $this->instantPurchase->getOption($store, $customer);
        $data = [
            'available' => $instantPurchaseOption->isAvailable()
        ];
        if (!$instantPurchaseOption->isAvailable()) {
            return $data;
        }

        $paymentToken = $instantPurchaseOption->getPaymentToken();
        $shippingAddress = $instantPurchaseOption->getShippingAddress();
        $billingAddress = $instantPurchaseOption->getBillingAddress();
        $shippingMethod = $instantPurchaseOption->getShippingMethod();
        $data += [
            'paymentToken' => [
                'publicHash' => $paymentToken->getPublicHash(),
                'summary' => $this->paymentTokenFormatter->format($paymentToken),
            ],
            'shippingAddress' => [
                'id' => $shippingAddress->getId(),
                'summary' => $this->customerAddressesFormatter->format($shippingAddress),
            ],
            'billingAddress' => [
                'id' => $billingAddress->getId(),
                'summary' => $this->customerAddressesFormatter->format($billingAddress),
            ],
            'shippingMethod' => [
                'carrier' => $shippingMethod->getCarrierCode(),
                'method' => $shippingMethod->getMethodCode(),
                'summary' => $this->shippingMethodFormatter->format($shippingMethod),
            ]
        ];

        return $data;
    }
}
