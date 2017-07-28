<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Helper\Mysql;

use Magento\Framework\App\ResourceConnection;

/**
 * Class \Magento\Framework\DB\Helper\Mysql\Fulltext
 *
 * @since 2.0.0
 */
class Fulltext
{
    /**
     * FULLTEXT search in MySQL search mode "natural language"
     */
    const FULLTEXT_MODE_NATURAL = 'IN NATURAL LANGUAGE MODE';

    /**
     * FULLTEXT search in MySQL search mode "natural language with query expansion"
     */
    const FULLTEXT_MODE_NATURAL_QUERY = 'IN NATURAL LANGUAGE MODE WITH QUERY EXPANSION';

    /**
     * FULLTEXT search in MySQL search mode "boolean"
     */
    const FULLTEXT_MODE_BOOLEAN = 'IN BOOLEAN MODE';

    /**
     * FULLTEXT search in MySQL search mode "query expansion"
     */
    const FULLTEXT_MODE_QUERY = 'WITH QUERY EXPANSION';

    /**
     * FULLTEXT search in MySQL MATCH method
     */
    const MATCH = 'MATCH';

    /**
     * FULLTEXT search in MySQL AGAINST method
     */
    const AGAINST = 'AGAINST';

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     * @since 2.0.0
     */
    private $connection;

    /**
     * @param ResourceConnection $resource
     * @since 2.0.0
     */
    public function __construct(ResourceConnection $resource)
    {
        $this->connection = $resource->getConnection();
    }
    
    /**
     * Method for FULLTEXT search in Mysql, will generated MATCH ($columns) AGAINST ('$expression' $mode)
     *
     * @param string|string[] $columns Columns which add to MATCH ()
     * @param string $expression Expression which add to AGAINST ()
     * @param string $mode
     * @return string
     * @since 2.0.0
     */
    public function getMatchQuery($columns, $expression, $mode = self::FULLTEXT_MODE_NATURAL)
    {
        if (is_array($columns)) {
            $columns = implode(', ', $columns);
        }

        $expression = $this->connection->quote($expression);

        $condition = self::MATCH . " ({$columns}) " . self::AGAINST . " ({$expression} {$mode})";
        return $condition;
    }

    /**
     * Method for FULLTEXT search in Mysql, will added generated
     * MATCH ($columns) AGAINST ('$expression' $mode) to where clause
     *
     * @param \Magento\Framework\DB\Select $select
     * @param string|string[] $columns Columns which add to MATCH ()
     * @param string $expression Expression which add to AGAINST ()
     * @param bool $isCondition true=AND, false=OR
     * @param string $mode
     * @return \Magento\Framework\DB\Select
     * @since 2.0.0
     */
    public function match($select, $columns, $expression, $isCondition = true, $mode = self::FULLTEXT_MODE_NATURAL)
    {
        $fullCondition = $this->getMatchQuery($columns, $expression, $mode);

        if ($isCondition) {
            $select->where($fullCondition);
        } else {
            $select->orWhere($fullCondition);
        }

        return $select;
    }
}
