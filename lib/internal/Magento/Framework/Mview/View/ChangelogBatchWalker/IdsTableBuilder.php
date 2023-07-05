<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Mview\View\ChangelogBatchWalker;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Mview\View\ChangelogInterface;

class IdsTableBuilder implements IdsTableBuilderInterface
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private ResourceConnection $resourceConnection;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @inheritdoc
     */
    public function build(ChangelogInterface $changelog): Table
    {
        $tableName = $this->resourceConnection->getTableName($this->generateTableName($changelog));
        $connection = $this->resourceConnection->getConnection();

        $table = $connection->newTable($tableName);
        $table->addColumn(
            self::FIELD_ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true, 'identity' => true],
            'ID'
        );
        $table->addColumn(
            $changelog->getColumnName(),
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Entity ID'
        );
        $table->setOption('type', 'memory');
        $table->addIndex(
            self::INDEX_NAME_UNIQUE,
            [
                $changelog->getColumnName()
            ],
            [
                'type' => AdapterInterface::INDEX_TYPE_UNIQUE
            ]
        );

        return $table;
    }

    /**
     * Generate unique name for temporary table used to prepare unique IDs of changes entries
     *
     * @param \Magento\Framework\Mview\View\ChangelogInterface $changelog
     * @return string
     */
    private function generateTableName(ChangelogInterface $changelog): string
    {
        $suffix = str_replace('.', '_', uniqid(self::TABLE_NAME_SUFFIX, true));

        return "{$changelog->getName()}_$suffix";
    }
}
