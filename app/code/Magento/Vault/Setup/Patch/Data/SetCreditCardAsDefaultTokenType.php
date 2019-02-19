<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Vault\Setup\Patch\Data;

use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\CreditCardTokenFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class SetCreditCardAsDefaultTokenType
 * @package Magento\Vault\Setup\Patch
 */
class SetCreditCardAsDefaultTokenType implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * SetCreditCardAsDefaultTokenType constructor.
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        // data update for Vault module < 2.0.1
        // update sets credit card as default token type
        $this->moduleDataSetup->getConnection()->update(
            $this->moduleDataSetup->getTable('vault_payment_token'),
            [
                PaymentTokenInterface::TYPE => CreditCardTokenFactory::TOKEN_TYPE_CREDIT_CARD
            ],
            PaymentTokenInterface::TYPE . ' = ""'
        );

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.1';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
