<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Payment\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Vault\Model\VaultPaymentInterface;

class VaultConfigProvider implements ConfigProviderInterface
{
    const IS_ACTIVE_CODE = 'is_active_payment_token_enabler';

    /**
     * @var string
     */
    private static $vaultCode = 'vault';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var SessionManagerInterface
     */
    private $session;

    /**
     * @var Data
     */
    private $paymentDataHelper;

    /**
     * VaultConfigProvider constructor.
     * @param StoreManagerInterface $storeManager
     * @param SessionManagerInterface $session
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        SessionManagerInterface $session
    ) {
        $this->storeManager = $storeManager;
        $this->session = $session;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $availableMethods = [];
        $vaultPayments = $this->getVaultPaymentMethodList();
        $customerId = $this->session->getCustomerId();
        $storeId = $this->storeManager->getStore()->getId();

        foreach ($vaultPayments as $method) {
            $availableMethods[$method->getCode()] = [
                'is_enabled' => $customerId !== null && $method->isActive($storeId)
            ];
        }

        return [
            self::$vaultCode => $availableMethods
        ];
    }

    /**
     * Get list of active Vault payment methods
     * @return array
     */
    private function getVaultPaymentMethodList()
    {
        $availableMethods = [];
        $storeId = $this->storeManager->getStore()->getId();

        $paymentMethods = $this->getPaymentDataHelper()->getStoreMethods($storeId);
        foreach ($paymentMethods as $method) {
            /** VaultPaymentInterface $method */
            if (!$method instanceof VaultPaymentInterface) {
                continue;
            }
            $availableMethods[] = $method;
        }

        return $availableMethods;
    }

    /**
     * Get payment data helper instance
     * @return Data
     * @deprecated
     */
    private function getPaymentDataHelper()
    {
        if ($this->paymentDataHelper === null) {
            $this->paymentDataHelper = ObjectManager::getInstance()->get(Data::class);
        }
        return $this->paymentDataHelper;
    }
}
