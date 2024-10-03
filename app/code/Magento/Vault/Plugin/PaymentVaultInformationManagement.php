<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Vault\Plugin;

use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Vault\Api\PaymentMethodListInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Payment vault information management process
 */
class PaymentVaultInformationManagement
{
    /**
     * @var PaymentMethodListInterface
     */
    private $vaultPaymentMethodList;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * PaymentVaultInformationManagement constructor.
     *
     * @param PaymentMethodListInterface $vaultPaymentMethodList
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        PaymentMethodListInterface $vaultPaymentMethodList,
        StoreManagerInterface $storeManager
    ) {
        $this->vaultPaymentMethodList = $vaultPaymentMethodList;
        $this->storeManager = $storeManager;
    }

    /**
     * Set available vault method code without index to payment
     *
     * @param PaymentInformationManagementInterface $subject
     * @param string $cartId
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSavePaymentInformation(
        PaymentInformationManagementInterface $subject,
        string $cartId,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ): void {
        $availableMethods = $this->vaultPaymentMethodList->getActiveList($this->storeManager->getStore()->getId());
        foreach ($availableMethods as $availableMethod) {
            if (strpos($paymentMethod->getMethod() ?? '', $availableMethod->getCode()) !== false) {
                $paymentMethod->setMethod($availableMethod->getCode());
            }
        }
    }
}
