<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Filter;

use Magento\Framework\Search\Adapter\Mysql\Query\QueryContainer;
use Magento\Framework\Search\Request\FilterInterface as RequestFilterInterface;

interface BuilderInterface
{
    /**
     * @param RequestFilterInterface $filter
     * @param string $conditionType
     * @param QueryContainer $queryContainer
     * @return string
     */
    public function build(RequestFilterInterface $filter, $conditionType, QueryContainer $queryContainer);
}
