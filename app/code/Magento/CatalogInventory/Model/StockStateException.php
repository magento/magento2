<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model;

use Magento\Framework\Exception\LocalizedException;

/**
 * Exception class reflecting when an operation cannot be completed due to the current stock status of an inventory item
 *
 * @api
 */
class StockStateException extends LocalizedException
{
}
