<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer\Fulltext;

use Magento\Framework\Indexer\AbstractProcessor;
use Magento\CatalogSearch\Model\Indexer\Fulltext;

/**
 * Class Processor
 * @api
 * @since 100.1.0
 * @deprecated CatalogSearch will be removed in 2.4, and {@see \Magento\ElasticSearch}
 *             will replace it as the default search engine.
 */
class Processor extends AbstractProcessor
{
    /**
     * Indexer ID
     */
    const INDEXER_ID = Fulltext::INDEXER_ID;
}
