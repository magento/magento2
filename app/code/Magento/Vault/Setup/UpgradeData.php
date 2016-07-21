<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\CreditCardTokenFactory;

/**
 * Class UpgradeData
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @inheritdoc
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $connection = $setup->getConnection();
            $connection->update($setup->getTable(InstallSchema::PAYMENT_TOKEN_TABLE), [
                PaymentTokenInterface::TYPE => CreditCardTokenFactory::TOKEN_TYPE_CREDIT_CARD
            ], PaymentTokenInterface::TYPE . ' = ""');
        }
        $setup->endSetup();
    }
}
