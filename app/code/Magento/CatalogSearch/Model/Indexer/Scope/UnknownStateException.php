<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\CatalogSearch\Model\Indexer\Scope;

use Magento\Framework\Exception\LocalizedException;

/**
 * Exception for situation where used state which is not defined in configuration
 *
 * @api
 * @since 100.2.0
 * @deprecated 101.0.0
 * @see \Magento\ElasticSearch
 */
class UnknownStateException extends LocalizedException
{
}
