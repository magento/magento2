<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Filter;

use Magento\Framework\Search\Request\FilterInterface;

/**
 * Interface \Magento\Framework\Search\Adapter\Mysql\Filter\PreprocessorInterface
 *
 * @since 2.0.0
 */
interface PreprocessorInterface
{
    /**
     * @param FilterInterface $filter
     * @param bool $isNegation
     * @param string $query
     * @return string
     * @since 2.0.0
     */
    public function process(FilterInterface $filter, $isNegation, $query);
}
