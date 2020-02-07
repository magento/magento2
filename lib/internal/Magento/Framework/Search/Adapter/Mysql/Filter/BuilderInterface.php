<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Filter;

use Magento\Framework\Search\Request\FilterInterface as RequestFilterInterface;

/**
 * MySQL search filter builder.
 *
 * @deprecated 102.0.0
 * @see \Magento\ElasticSearch
 */
interface BuilderInterface
{
    /**
     * Buil filter.
     *
     * @param RequestFilterInterface $filter
     * @param string $conditionType
     * @return string
     */
    public function build(RequestFilterInterface $filter, $conditionType);
}
