<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Gateway\Request;

use Magento\BraintreeTwo\Gateway\Helper\SubjectReader;
use Magento\BraintreeTwo\Model\Ui\ConfigProvider;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Vault\Gateway\Config\Config;
use Magento\Vault\Model\VaultPaymentInterface;

/**
 * Vault Data Builder
 */
class VaultDataBuilder implements BuilderInterface
{
    /**
     * Additional options in request to gateway
     */
    const OPTIONS = 'options';

    /**
     * The option that determines whether the payment method
     * associated with the successful transaction should be stored in the Vault.
     */
    const STORE_IN_VAULT = 'storeInVault';

    /**
     * The option that determines whether the shipping address information
     * provided with the transaction should be associated with the customer ID specified.
     * When passed, the payment method will always be stored in the Vault.
     */
    const STORE_IN_VAULT_ON_SUCCESS = 'storeInVaultOnSuccess';

    /**
     * "Is active" vault module config option name
     */
    const CONFIG_PAYMENT_VAULT_ACTIVE = 'active';

    /**
     * @var VaultPaymentInterface
     */
    protected $vaultPayment;

    /**
     * @param VaultPaymentInterface $vaultPayment
     */
    public function __construct(VaultPaymentInterface $vaultPayment)
    {
        $this->vaultPayment = $vaultPayment;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $result = [];

        $isActiveVaultModule = $this->vaultPayment->isActiveForPayment(ConfigProvider::CODE);
        if ($isActiveVaultModule) {
            $result[self::OPTIONS][self::STORE_IN_VAULT_ON_SUCCESS] = true;
        }

        return $result;
    }
}
