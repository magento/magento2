<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Sql;

use Magento\Framework\App\Resource;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Class Concat
 */
class ConcatExpression extends \Zend_Db_Expr
{
    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @var string[]
     */
    protected $columns;

    /**
     * @var string
     */
    protected $separator;

    /**
     * @param Resource $resource
     * @param array $columns
     * @param string $separator
     */
    public function __construct(
        Resource $resource,
        array $columns,
        $separator = ' '
    ) {
        $this->adapter = $resource->getConnection();
        $this->columns = $columns;
        $this->separator = $separator;
    }

    /**
     * Returns SQL expression
     *   TRIM(CONCAT_WS(separator, str1, str2, ...))
     *
     * @return string
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
            $columns[] = $column;
        }
        return sprintf(
            'TRIM(%s)',
            $this->adapter->getConcatSql($columns, ' ')
        );
    }
}
