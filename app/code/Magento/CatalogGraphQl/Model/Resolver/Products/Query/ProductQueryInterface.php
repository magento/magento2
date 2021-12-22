<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogGraphQl\Model\Resolver\Products\Query;

use Magento\CatalogGraphQl\Model\Resolver\Products\SearchResult;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * Search for products by criteria
 */
interface ProductQueryInterface
{
    /**
     * Get product search result
     *
     * @param array $args
     * @param ResolveInfo $info
     * @param ContextInterface $context
     * @return SearchResult
     */
    public function getResult(array $args, ResolveInfo $info, ContextInterface $context): SearchResult;
}
