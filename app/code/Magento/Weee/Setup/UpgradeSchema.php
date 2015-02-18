<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '2.0.0.1') < 0) {
            $setup->startSetup();
            $connection = $setup->getConnection();

            //Drop entity_type_id column for wee tax table
            $connection->dropColumn($setup->getTable('weee_tax'), 'entity_type_id');

            $setup->endSetup();
        }
    }
}