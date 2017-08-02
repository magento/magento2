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
 * @since 2.0.0
 */
class Product extends \Magento\Catalog\Model\Product
{
    /**
     * DO NOT Initialize resources.
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
    }
}
