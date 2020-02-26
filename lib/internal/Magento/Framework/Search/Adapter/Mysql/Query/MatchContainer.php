<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Search\Adapter\Mysql\Query;

use Magento\Framework\Search\Request\QueryInterface;

// @codeCoverageIgnore

/**
 * MySQL search query match container.
 *
 * @deprecated 102.0.0
 * @see \Magento\ElasticSearch
 */
class MatchContainer
{
    /**
     * @var QueryInterface
     */
    private $request;

    /**
     * @var string
     */
    private $conditionType;

    /**
     * @param QueryInterface $request
     * @param string $conditionType
     * @internal param string $name
     */
    public function __construct(QueryInterface $request, $conditionType)
    {
        $this->request = $request;
        $this->conditionType = $conditionType;
    }

    /**
     * Get request.
     *
     * @return QueryInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get condition type.
     *
     * @return string
     */
    public function getConditionType()
    {
        return $this->conditionType;
    }
}
