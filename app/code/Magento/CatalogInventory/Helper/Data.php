<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Helper;

/**
 * Catalog Inventory default helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Error codes, that Catalog Inventory module can set to quote or quote items
     */
    const ERROR_QTY = 1;

    /**
     * Error qty increments
     */
    const ERROR_QTY_INCREMENTS = 2;
}
