<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB;

use Magento\Framework\App\Resource;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Class Concat
 */
class ConcatExpression extends \Zend_Db_Expr
{
    /**
     * @var Resource
     */
    protected $resource;

    /**
     * @var string[]
     */
    protected $columns;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var string
     */
    protected $separator;

    /**
     * @var string
     */
    protected $isTableAlias;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param array $columns
     * @param string $tableName
     * @param bool|false $isTableAlias
     * @param string $separator
     */
    public function __construct(
        Resource $resource,
        array $columns,
        $tableName,
        $isTableAlias = false,
        $separator = ' '
    ) {
        $this->resource = $resource;
        $this->columns = $columns;
        $this->tableName = $tableName;
        $this->isTableAlias = $isTableAlias;
        $this->separator = $separator;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $columns = [];
        foreach ($this->columns as $key => $column) {
            $columns[$key] = sprintf(
                "ifnull(%s, '')",
                $this->resource->getConnection(Resource::DEFAULT_READ_RESOURCE)
                    ->quoteIdentifier($this->tableName . '.' .$column)
            );
        }
        return sprintf(
            'trim(concat(%s))',
            implode(
                sprintf(", '%s' ,", $this->separator),
                $columns
            )
        );
    }
}
