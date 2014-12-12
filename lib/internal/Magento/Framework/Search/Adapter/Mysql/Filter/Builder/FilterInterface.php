<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Search\Adapter\Mysql\Filter\Builder;

use Magento\Framework\Search\Request\FilterInterface as RequestFilterInterface;

interface FilterInterface
{
    /**
     * @param RequestFilterInterface $filter
     * @param bool $isNegation
     * @return string
     */
    public function buildFilter(
        RequestFilterInterface $filter,
        $isNegation
    );
}
