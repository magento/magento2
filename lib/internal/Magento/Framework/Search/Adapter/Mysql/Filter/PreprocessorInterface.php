<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Filter;

use Magento\Framework\Search\Request\FilterInterface;

/**
 * MySQL search filter pre-processor.
 *
 * @deprecated
 * @see \Magento\ElasticSearch
 */
interface PreprocessorInterface
{
    /**
     * Process filter.
     *
     * @param FilterInterface $filter
     * @param bool $isNegation
     * @param string $query
     * @return string
     */
    public function process(FilterInterface $filter, $isNegation, $query);
}
