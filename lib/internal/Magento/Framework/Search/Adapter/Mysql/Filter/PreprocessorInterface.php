<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Filter;

use Magento\Framework\Search\Request\FilterInterface;

interface PreprocessorInterface
{
    /**
     * @param FilterInterface $filter
     * @param bool $isNegation
     * @param string $query
     * @return string
     */
    public function process(FilterInterface $filter, $isNegation, $query);
}
