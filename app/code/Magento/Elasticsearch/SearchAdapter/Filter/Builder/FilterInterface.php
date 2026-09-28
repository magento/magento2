<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Elasticsearch\SearchAdapter\Filter\Builder;

use Magento\Framework\Search\Request\FilterInterface as RequestFilterInterface;

/**
 * @api
 * @since 100.1.0
 */
interface FilterInterface
{
    /**
     * @param RequestFilterInterface $filter
     * @return array
     * @since 100.1.0
     */
    public function buildFilter(RequestFilterInterface $filter);
}
