<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Search\Adapter\Mysql\Query;

use Magento\Framework\Search\Request\QueryInterface;

// @codeCoverageIgnore

/**
 * Class \Magento\Framework\Search\Adapter\Mysql\Query\MatchContainer
 *
 * @since 2.0.0
 */
class MatchContainer
{
    /**
     * @var QueryInterface
     * @since 2.0.0
     */
    private $request;

    /**
     * @var string
     * @since 2.0.0
     */
    private $conditionType;

    /**
     * @param QueryInterface $request
     * @param string $conditionType
     * @internal param string $name
     * @since 2.0.0
     */
    public function __construct(QueryInterface $request, $conditionType)
    {
        $this->request = $request;
        $this->conditionType = $conditionType;
    }

    /**
     * @return QueryInterface
     * @since 2.0.0
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getConditionType()
    {
        return $this->conditionType;
    }
}
