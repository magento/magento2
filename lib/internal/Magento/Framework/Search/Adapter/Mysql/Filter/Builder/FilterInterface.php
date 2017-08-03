<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Filter\Builder;

use Magento\Framework\Search\Request\FilterInterface as RequestFilterInterface;

/**
 * Interface \Magento\Framework\Search\Adapter\Mysql\Filter\Builder\FilterInterface
 *
 * @since 2.0.0
 */
interface FilterInterface
{
    /**
     * @param RequestFilterInterface $filter
     * @param bool $isNegation
     * @return string
     * @since 2.0.0
     */
    public function buildFilter(
        RequestFilterInterface $filter,
        $isNegation
    );
}
