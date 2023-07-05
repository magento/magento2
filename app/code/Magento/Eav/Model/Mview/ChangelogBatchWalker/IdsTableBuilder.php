<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\Mview\ChangelogBatchWalker;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Mview\View\ChangelogBatchWalker\IdsTableBuilder as BaseIdsTableBuilder;
use Magento\Framework\Mview\View\ChangelogInterface;

class IdsTableBuilder extends BaseIdsTableBuilder
{
    /**
     * @inheritdoc
     */
    public function build(ChangelogInterface $changelog): Table
    {
        $table = parent::build($changelog);
        $table->addColumn(
            'attribute_ids',
            Table::TYPE_TEXT,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Attribute IDs'
        );
        $table->addColumn(
            'store_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Store ID'
        );
        $table->addIndex(
            self::INDEX_NAME_UNIQUE,
            [
                $changelog->getColumnName(),
                'attribute_ids',
                'store_id'
            ],
            [
                'type' => AdapterInterface::INDEX_TYPE_UNIQUE
            ]
        );

        return $table;
    }
}
