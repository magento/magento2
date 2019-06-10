<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Filter\Builder;

use Magento\Framework\Search\Request\FilterInterface as RequestFilterInterface;

/**
 * MySQL search filter builder.
 *
 * @deprecated 102.0.0
 * @see \Magento\ElasticSearch
 */
interface FilterInterface
{
    /**
     * Build filter.
     *
     * @param RequestFilterInterface $filter
     * @param bool $isNegation
     * @return string
     */
    public function buildFilter(
        RequestFilterInterface $filter,
        $isNegation
    );
}
