<?php

declare(strict_types=1);

namespace Magento\SalesSequence\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context): void
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $this->addSequenceMetaStoreIdForeignKey($setup);
        }

        $setup->endSetup();
    }

    private function addSequenceMetaStoreIdForeignKey(SchemaSetupInterface $setup): void
    {
        $setup->getConnection()->addForeignKey(
            $setup->getFkName('sales_sequence_meta', 'store_id', 'store', 'store_id'),
            $setup->getTable('sales_sequence_meta'),
            'store_id',
            $setup->getTable('store'),
            'store_id'
        );
    }
}
