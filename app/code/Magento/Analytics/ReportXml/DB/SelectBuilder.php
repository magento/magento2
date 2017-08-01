<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\ReportXml\DB;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

/**
 * Class SelectBuilder
 *
 * Responsible for Select object creation, works as a builder. Returns Select as result;
 * Used in SQL assemblers.
 * @since 2.2.0
 */
class SelectBuilder
{
    /**
     * @var ResourceConnection
     * @since 2.2.0
     */
    private $resourceConnection;

    /**
     * @var string
     * @since 2.2.0
     */
    private $connectionName;

    /**
     * @var array
     * @since 2.2.0
     */
    private $from;

    /**
     * @var array
     * @since 2.2.0
     */
    private $group = [];

    /**
     * @var array
     * @since 2.2.0
     */
    private $columns = [];

    /**
     * @var array
     * @since 2.2.0
     */
    private $filters = [];

    /**
     * @var array
     * @since 2.2.0
     */
    private $joins = [];

    /**
     * @var array
     * @since 2.2.0
     */
    private $params = [];

    /**
     * @var array
     * @since 2.2.0
     */
    private $having = [];

    /**
     * SelectBuilder constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @since 2.2.0
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Get join condition
     *
     * @return array
     * @since 2.2.0
     */
    public function getJoins()
    {
        return $this->joins;
    }

    /**
     * Set joins conditions
     *
     * @param array $joins
     * @return void
     * @since 2.2.0
     */
    public function setJoins($joins)
    {
        $this->joins = $joins;
    }

    /**
     * Get connection name
     *
     * @return string
     * @since 2.2.0
     */
    public function getConnectionName()
    {
        return $this->connectionName;
    }

    /**
     * Set connection name
     *
     * @param string $connectionName
     * @return void
     * @since 2.2.0
     */
    public function setConnectionName($connectionName)
    {
        $this->connectionName = $connectionName;
    }

    /**
     * Get columns
     *
     * @return array
     * @since 2.2.0
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Set columns
     *
     * @param array $columns
     * @return void
     * @since 2.2.0
     */
    public function setColumns($columns)
    {
        $this->columns = $columns;
    }

    /**
     * Get filters
     *
     * @return array
     * @since 2.2.0
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Set filters
     *
     * @param array $filters
     * @return void
     * @since 2.2.0
     */
    public function setFilters($filters)
    {
        $this->filters = $filters;
    }

    /**
     * Get from condition
     *
     * @return array
     * @since 2.2.0
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Set from condition
     *
     * @param array $from
     * @return void
     * @since 2.2.0
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }

    /**
     * Process JOIN conditions
     *
     * @param Select $select
     * @param array $joinConfig
     * @return Select
     * @since 2.2.0
     */
    private function processJoin(Select $select, $joinConfig)
    {
        switch ($joinConfig['link-type']) {
            case 'left':
                $select->joinLeft($joinConfig['table'], $joinConfig['condition'], []);
                break;
            case 'inner':
                $select->joinInner($joinConfig['table'], $joinConfig['condition'], []);
                break;
            case 'right':
                $select->joinRight($joinConfig['table'], $joinConfig['condition'], []);
                break;
        }
        return $select;
    }

    /**
     * Creates Select object
     *
     * @return Select
     * @since 2.2.0
     */
    public function create()
    {
        $connection = $this->resourceConnection->getConnection($this->getConnectionName());
        $select = $connection->select();
        $select->from($this->getFrom(), []);
        $select->columns($this->getColumns());
        foreach ($this->getFilters() as $filter) {
            $select->where($filter);
        }
        foreach ($this->getJoins() as $joinConfig) {
            $select = $this->processJoin($select, $joinConfig);
        }
        if (!empty($this->getGroup())) {
            $select->group(implode(', ', $this->getGroup()));
        }
        return $select;
    }

    /**
     * Returns group
     *
     * @return array
     * @since 2.2.0
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set group
     *
     * @param array $group
     * @return void
     * @since 2.2.0
     */
    public function setGroup($group)
    {
        $this->group = $group;
    }

    /**
     * Get parameters
     *
     * @return array
     * @since 2.2.0
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set parameters
     *
     * @param array $params
     * @return void
     * @since 2.2.0
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * Get having condition
     *
     * @return array
     * @since 2.2.0
     */
    public function getHaving()
    {
        return $this->having;
    }

    /**
     * Set having condition
     *
     * @param array $having
     * @return void
     * @since 2.2.0
     */
    public function setHaving($having)
    {
        $this->having = $having;
    }
}
