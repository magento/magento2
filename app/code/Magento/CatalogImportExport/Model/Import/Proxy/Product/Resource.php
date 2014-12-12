<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Import proxy product resource
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\CatalogImportExport\Model\Import\Proxy\Product;

class Resource extends \Magento\Catalog\Model\Resource\Product
{
    /**
     * Product to category table.
     *
     * @return string
     */
    public function getProductCategoryTable()
    {
        return $this->_productCategoryTable;
    }

    /**
     * Product to website table.
     *
     * @return string
     */
    public function getProductWebsiteTable()
    {
        return $this->_productWebsiteTable;
    }
}
