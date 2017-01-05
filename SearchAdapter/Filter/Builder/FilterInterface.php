<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
