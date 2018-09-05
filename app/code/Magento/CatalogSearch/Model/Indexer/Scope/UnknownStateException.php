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
 * @since 100.2.0
 * @deprecated
 * @see ElasticSearch module is default search engine starting from 2.3. CatalogSearch would be removed in 2.4
 */
class UnknownStateException extends LocalizedException
{
}
