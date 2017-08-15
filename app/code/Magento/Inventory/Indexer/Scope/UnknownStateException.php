<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Indexer\Scope;

use Magento\Framework\Exception\LocalizedException;

/**
 * Exception for situation where used state which is not defined in configuration
 * @todo refactoring it copy from catalog search module
 * @api
 */
class UnknownStateException extends LocalizedException
{
}
