<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Helper;

/**
 * Catalog search helper
 *
 * @api
 * @since 2.0.0
 */
class Data extends \Magento\Search\Helper\Data
{
    /**
     * Retrieve advanced search URL
     *
     * @return string
     * @since 2.0.0
     */
    public function getAdvancedSearchUrl()
    {
        return $this->_getUrl('catalogsearch/advanced');
    }
}
