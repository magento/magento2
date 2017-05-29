<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Indexer\Scope;

use Magento\Framework\Exception\LocalizedException;

/**
 * Exception which represents situation where temporary index table should be used somewhere,
 * but it does not exist in a database
 *
 * @api
 */
class IndexTableNotExistException extends LocalizedException
{
}
