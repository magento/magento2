<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Cms\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * @inheritDoc
 */
class Recurring implements InstallSchemaInterface
{
    /**
     * @inheritDoc
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->addLayoutSelected($setup);
    }

    /**
     * Add custom layout selected field.
     *
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function addLayoutSelected(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable('cms_page');
        $connection = $setup->getConnection();
        $column = 'layout_update_selected';
        if (!$connection->tableColumnExists($table, $column)) {
            $connection->addColumn(
                $table,
                $column,
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'File containing custom layout update'
                ]
            );
        }
    }
}
