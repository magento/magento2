<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Adapter\Mysql\Filter;


use Magento\CatalogSearch\Model\Search\RequestGenerator;

/**
 * Purpose of class is to resolve table alias for Search Request filter
 */
class AliasResolver
{
    /**
     * The suffix for stock status filter that may be added to the query beside the filter query
     * Used when showing of Out of Stock products is disabled.
     */
    const STOCK_FILTER_SUFFIX = '_stock';

    /**
     * @param \Magento\Framework\Search\Request\FilterInterface $filter
     * @return string alias of the filter in database
     */
    public function getAlias(\Magento\Framework\Search\Request\FilterInterface $filter)
    {
        $alias = null;
        $field = $filter->getField();
        switch ($field) {
            case 'price':
                $alias = 'price_index';
                break;
            case 'category_ids':
                $alias = 'category_ids_index';
                break;
            default:
                $alias = $field . RequestGenerator::FILTER_SUFFIX;
                break;
        }
        return $alias;
    }
}
