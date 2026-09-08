<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\Vault\Setup\Patch\Data;

use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\CreditCardTokenFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Sets credit card as the default vault payment token type for rows upgraded from Vault < 2.0.1.
 */
class SetCreditCardAsDefaultTokenType implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        // data update for Vault module < 2.0.1
        // update sets credit card as default token type
        // (ANSI_QUOTES-off syntax); Postgres double quotes always mean an identifier, so
        // this rendered as a zero-length identifier reference instead of comparing
        // against an empty string. Single-quoted is valid on both.
        $this->moduleDataSetup->getConnection()->update(
            $this->moduleDataSetup->getTable('vault_payment_token'),
            [
                PaymentTokenInterface::TYPE => CreditCardTokenFactory::TOKEN_TYPE_CREDIT_CARD
            ],
            PaymentTokenInterface::TYPE . " = ''"
        );

        $this->moduleDataSetup->getConnection()->endSetup();
        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '2.0.1';
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
