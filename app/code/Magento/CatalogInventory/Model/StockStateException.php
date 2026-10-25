<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
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
