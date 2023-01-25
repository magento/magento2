<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Mview\View\AdditionalColumnsProcessor;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Mview\View\AdditionalColumnProcessorInterface;

class DefaultProcessor implements AdditionalColumnProcessorInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @inheritDoc
     */
    public function getTriggerColumns(string $eventPrefix, array $additionalColumns): array
    {
        $resource = $this->resourceConnection->getConnection();
        $triggersColumns = [
            'column_names' => [],
            'column_values' => []
        ];

        foreach ($additionalColumns as $additionalColumn) {
            $triggersColumns['column_names'][$additionalColumn['name']] = $resource->quoteIdentifier(
                $additionalColumn['cl_name']
            );

            $triggersColumns['column_values'][$additionalColumn['name']] = isset($additionalColumn['constant']) ?
                $resource->quote($additionalColumn['constant']) :
                $eventPrefix . $resource->quoteIdentifier($additionalColumn['name']);
        }

        return $triggersColumns;
    }

    /**
     * @return string
     */
    public function getPreStatements(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function processColumnForCLTable(Table $table, string $columnName): void
    {
        $table->addColumn(
            $columnName,
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['unsigned' => true, 'nullable' => true, 'default' => null],
            $columnName
        );
    }
}
