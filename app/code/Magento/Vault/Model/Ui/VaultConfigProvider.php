<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Payment\Api\Data\PaymentMethodInterface;
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
     * @var \Magento\Payment\Api\PaymentMethodListInterface
     */
    private $paymentMethodList;

    /**
     * @var \Magento\Payment\Model\Method\InstanceFactory
     */
    private $paymentMethodInstanceFactory;

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
     * Get list of active Vault payment methods.
     *
     * @return VaultPaymentInterface[]
     */
    private function getVaultPaymentMethodList()
    {
        $storeId = $this->storeManager->getStore()->getId();

        $paymentMethods = array_map(
            function (PaymentMethodInterface $paymentMethod) {
                return $this->getPaymentMethodInstanceFactory()->create($paymentMethod);
            },
            $this->getPaymentMethodList()->getActiveList($storeId)
        );

        $availableMethods = array_filter(
            $paymentMethods,
            function (\Magento\Payment\Model\MethodInterface $methodInstance) {
                return $methodInstance instanceof VaultPaymentInterface;
            }
        );

        return $availableMethods;
    }

    /**
     * Get payment method list.
     *
     * @return \Magento\Payment\Api\PaymentMethodListInterface
     * @deprecated
     */
    private function getPaymentMethodList()
    {
        if ($this->paymentMethodList === null) {
            $this->paymentMethodList = ObjectManager::getInstance()->get(
                \Magento\Payment\Api\PaymentMethodListInterface::class
            );
        }
        return $this->paymentMethodList;
    }

    /**
     * Get payment method instance factory.
     *
     * @return \Magento\Payment\Model\Method\InstanceFactory
     * @deprecated
     */
    private function getPaymentMethodInstanceFactory()
    {
        if ($this->paymentMethodInstanceFactory === null) {
            $this->paymentMethodInstanceFactory = ObjectManager::getInstance()->get(
                \Magento\Payment\Model\Method\InstanceFactory::class
            );
        }
        return $this->paymentMethodInstanceFactory;
    }
}
