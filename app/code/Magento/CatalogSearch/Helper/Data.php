<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Helper;

use Magento\Store\Model\ScopeInterface;

/**
 * Catalog search helper
 *
 * @api
 * @since 100.0.2
 */
class Data extends \Magento\Search\Helper\Data
{
    const XML_PATH_SEARCH_DEFAULT_SORT_BY = 'catalog/search/default_sort_by';

    /**
     * Retrieve advanced search URL
     *
     * @return string
     */
    public function getAdvancedSearchUrl()
    {
        return $this->_getUrl('catalogsearch/advanced');
    }

    /**
     * Get config value for search results sort by option
     *
     * @return string
     */
    public function getDefaultSearchSortBy(): string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SEARCH_DEFAULT_SORT_BY, ScopeInterface::SCOPE_STORE);
    }
}
