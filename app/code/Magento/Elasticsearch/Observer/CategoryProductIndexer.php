<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Observer;

use Magento\CatalogSearch\Model\Indexer\Fulltext\Processor;
use Magento\Elasticsearch\Model\Config;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Checks if a category has changed products and depends on indexer configuration.
 */
class CategoryProductIndexer implements ObserverInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Processor
     */
    private $processor;

    /**
     * @param Config $config
     * @param Processor $processor
     */
    public function __construct(Config $config, Processor $processor)
    {
        $this->processor = $processor;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer): void
    {
        if (!$this->config->isElasticsearchEnabled()) {
            return;
        }

        $productIds = $observer->getEvent()->getProductIds();
        if (!empty($productIds) && $this->processor->isIndexerScheduled()) {
            $this->processor->markIndexerAsInvalid();
        }
    }
}
