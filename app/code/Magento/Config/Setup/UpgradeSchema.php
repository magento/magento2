<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Upgrade the AsynchronousOperations module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '2.1.0', '<')) {
            $this->addUpdatedAtField($setup);
        }
        $setup->endSetup();
    }

    /**
     * Add updated at column
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    protected function addUpdatedAtField(SchemaSetupInterface $setup)
    {
        if (!($setup->getConnection()->tableColumnExists(
            $setup->getTable('core_config_data'),
            'updated_at'
        ))) {
            $setup->getConnection()->addColumn(
                $setup->getTable('core_config_data'),
                'updated_at',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    'length' => null,
                    'nullable' => false,
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE,
                    'comment' => 'Updated At'
                ]
            );
        }

        return $this;
    }
}
