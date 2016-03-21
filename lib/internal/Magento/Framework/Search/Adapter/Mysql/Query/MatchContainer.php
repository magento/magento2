<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Search\Adapter\Mysql\Query;


use Magento\Framework\Search\Request\QueryInterface;

// @codeCoverageIgnore

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
     * @return QueryInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return string
     */
    public function getConditionType()
    {
        return $this->conditionType;
    }
}
