<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Filter;

use Magento\Framework\Search\Adapter\Mysql\Query\QueryContainer;
use Magento\Framework\Search\Request\FilterInterface;

interface PreprocessorInterface
{
    /**
     * @param FilterInterface $filter
     * @param bool $isNegation
     * @param string $query
     * @param QueryContainer $queryContainer
     * @return string
     */
    public function process(FilterInterface $filter, $isNegation, $query, QueryContainer $queryContainer);
}
