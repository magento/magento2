<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Sql;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Class Concat
 * @since 2.0.0
 */
class ConcatExpression extends Expression
{
    /**
     * @var AdapterInterface
     * @since 2.0.0
     */
    protected $adapter;

    /**
     * @var string[]
     * @since 2.0.0
     */
    protected $columns;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $separator;

    /**
     * @param ResourceConnection $resource
     * @param array $columns
     * @param string $separator
     * @since 2.0.0
     */
    public function __construct(
        ResourceConnection $resource,
        array $columns,
        $separator = ' '
    ) {
        $this->adapter = $resource->getConnection();
        $this->columns = $columns;
        $this->separator = $separator;
    }

    /**
     * Returns SQL expression
     *   TRIM(CONCAT_WS(separator, IF(str1 <> '', str1, NULL), IF(str2 <> '', str2, NULL) ...))
     *
     * @return string
     * @since 2.0.0
     */
    public function __toString()
    {
        $columns = [];
        foreach ($this->columns as $key => $part) {
            if (isset($part['columnName']) && $part['columnName'] instanceof \Zend_Db_Expr) {
                $column = $part['columnName'];
            } else {
                $column = $this->adapter->quoteIdentifier(
                    (isset($part['tableAlias']) ? $part['tableAlias'] . '.' : '')
                    . (isset($part['columnName']) ? $part['columnName'] : $key)
                );
            }
            $columns[] = $this->adapter->getCheckSql($column . " <> ''", $column, 'NULL');
        }
        return sprintf(
            'TRIM(%s)',
            $this->adapter->getConcatSql($columns, ' ')
        );
    }
}
