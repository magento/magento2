<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\CatalogSearch\Helper;

/**
 * Catalog search helper
 *
 * @api
 * @since 100.0.2
 */
class Data extends \Magento\Search\Helper\Data
{
    /**
     * Retrieve advanced search URL
     *
     * @return string
     */
    public function getAdvancedSearchUrl()
    {
        return $this->_getUrl('catalogsearch/advanced');
    }
}
