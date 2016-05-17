<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer\Fulltext;

use Magento\Framework\Indexer\AbstractProcessor;
use Magento\CatalogSearch\Model\Indexer\Fulltext;

/**
 * Class Processor
 */
class Processor extends AbstractProcessor
{
    /**
     * Indexer ID
     */
    const INDEXER_ID = Fulltext::INDEXER_ID;
}
