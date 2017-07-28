<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Search\Adapter\Mysql\Query;

use Magento\Framework\DB\Select;
use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;

/**
 * Class \Magento\Framework\Search\Adapter\Mysql\Query\QueryContainer
 *
 * @since 2.0.0
 */
class QueryContainer
{
    const DERIVED_QUERY_PREFIX = 'derived_';

    /**
     * @var array
     * @since 2.0.0
     */
    private $queries = [];

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\Query\MatchContainerFactory
     * @since 2.0.0
     */
    private $matchContainerFactory;

    /**
     * @param MatchContainerFactory $matchContainerFactory
     * @since 2.0.0
     */
    public function __construct(MatchContainerFactory $matchContainerFactory)
    {
        $this->matchContainerFactory = $matchContainerFactory;
    }

    /**
     * @param Select $select
     * @param RequestQueryInterface $query
     * @param string $conditionType
     * @return Select
     * @since 2.0.0
     */
    public function addMatchQuery(
        Select $select,
        RequestQueryInterface $query,
        $conditionType
    ) {
        $container = $this->matchContainerFactory->create(
            [
                'request' => $query,
                'conditionType' => $conditionType,
            ]
        );
        $name = self::DERIVED_QUERY_PREFIX . count($this->queries);
        $this->queries[$name] = $container;
        return $select;
    }

    /**
     * @return MatchContainer[]
     * @since 2.0.0
     */
    public function getMatchQueries()
    {
        return $this->queries;
    }
}
