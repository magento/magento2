<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\ReportXml\DB;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Sql\ColumnValueExpression;

/**
 * Class ColumnsResolver
 *
 * Resolves columns names
 * @since 2.2.0
 */
class ColumnsResolver
{
    /**
     * @var NameResolver
     * @since 2.2.0
     */
    private $nameResolver;

    /**
     * @var ResourceConnection
     * @since 2.2.0
     */
    private $resourceConnection;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     * @since 2.2.0
     */
    private $connection;

    /**
     * ColumnsResolver constructor.
     *
     * @param NameResolver $nameResolver
     * @param ResourceConnection $resourceConnection
     * @since 2.2.0
     */
    public function __construct(
        NameResolver $nameResolver,
        ResourceConnection $resourceConnection
    ) {
        $this->nameResolver = $nameResolver;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Returns connection
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     * @since 2.2.0
     */
    private function getConnection()
    {
        if (!$this->connection) {
            $this->connection = $this->resourceConnection->getConnection();
        }
        return $this->connection;
    }

    /**
     * Set columns list to SelectBuilder
     *
     * @param SelectBuilder $selectBuilder
     * @param array $entityConfig
     * @return array
     * @since 2.2.0
     */
    public function getColumns(SelectBuilder $selectBuilder, $entityConfig)
    {
        if (!isset($entityConfig['attribute'])) {
            return [];
        }
        $group = [];
        $columns = $selectBuilder->getColumns();
        foreach ($entityConfig['attribute'] as $attributeData) {
            $columnAlias = $this->nameResolver->getAlias($attributeData);
            $tableAlias = $this->nameResolver->getAlias($entityConfig);
            $columnName = $this->nameResolver->getName($attributeData);
            if (isset($attributeData['function'])) {
                $prefix = '';
                if (isset($attributeData['distinct']) && $attributeData['distinct'] == true) {
                    $prefix = ' DISTINCT ';
                }
                $expression = new ColumnValueExpression(
                    strtoupper($attributeData['function']) . '(' . $prefix
                    . $this->getConnection()->quoteIdentifier($tableAlias . '.' . $columnName)
                    . ')'
                );
            } else {
                $expression = $tableAlias . '.' . $columnName;
            }
            $columns[$columnAlias] = $expression;
            if (isset($attributeData['group'])) {
                $group[$columnAlias] = $expression;
            }
        }
        $selectBuilder->setGroup(array_merge($selectBuilder->getGroup(), $group));
        return $columns;
    }
}
