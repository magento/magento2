<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter\Filter\Builder;

use Magento\Framework\Search\Request\FilterInterface as RequestFilterInterface;

interface FilterInterface
{
    /**
     * @param RequestFilterInterface $filter
     * @return array
     */
    public function buildFilter(RequestFilterInterface $filter);
}
