<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Import proxy product model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\CatalogImportExport\Model\Import\Proxy;

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
