<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Indexer\Scope;

use Magento\Framework\Exception\LocalizedException;

/**
 * Exception for situation where used state which is not defined in configuration
 *
 * @api
 */
class UnknownStateException extends LocalizedException
{
}
