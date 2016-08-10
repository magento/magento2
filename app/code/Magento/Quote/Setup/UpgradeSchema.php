<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Upgrade the Catalog module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var string
     */
    private static $connectionName = 'checkout';

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $setup->getConnection(self::$connectionName)->addIndex(
                $setup->getTable('quote_id_mask', self::$connectionName),
                $setup->getIdxName('quote_id_mask', ['masked_id'], '', self::$connectionName),
                ['masked_id']
            );
        }

        if (version_compare($context->getVersion(), '2.0.2', '<')) {
            $setup->getConnection(self::$connectionName)->changeColumn(
                $setup->getTable('quote_address', self::$connectionName),
                'street',
                'street',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'comment' => 'Street'
                ]
            );
        }

        $setup->endSetup();
    }
}
