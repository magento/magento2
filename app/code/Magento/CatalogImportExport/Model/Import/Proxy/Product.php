<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Import proxy product model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\CatalogImportExport\Model\Import\Proxy;

/**
 * Class \Magento\CatalogImportExport\Model\Import\Proxy\Product
 *
 */
class Product extends \Magento\Catalog\Model\Product
{
    /**
     * DO NOT Initialize resources.
     *
     * @return void
     */
    protected function _construct()
    {
    }
}
