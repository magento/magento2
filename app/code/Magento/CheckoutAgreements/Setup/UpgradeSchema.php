<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Class UpgradeSchema
 *
 * Used to create/modify DB tables
 *
 * Fresh install processing order:
 * - InstallSchema
 * - UpgradeSchema (installed version will be equal to '' on fresh install)
 *
 * Upgrade processing order:
 * - UpgradeSchema(Runs if version in module.xml is greater than installed version)
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $connection = $setup->getConnection();

        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $column = [
                'type' => Table::TYPE_SMALLINT,
                'length' => 6,
                'nullable' => false,
                'comment' => 'Applied mode',
                'default' => '0'
            ];
            $connection->addColumn($setup->getTable('checkout_agreement'), 'mode', $column);
        }
    }
}
