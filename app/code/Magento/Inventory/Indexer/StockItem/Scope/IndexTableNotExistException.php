<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Indexer\StockItem\Scope;

use Magento\Framework\Exception\LocalizedException;

/**
 * Exception which represents situation where temporary index table should be used somewhere,
 * but it does not exist in a database
 * @todo refactoring it copy from catalog search module
 * @api
 */
class IndexTableNotExistException extends LocalizedException
{
}
