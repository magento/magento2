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
use Magento\Catalog\Model\Indexer\Category\Flat\State as FlatState;

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
     * @var FlatState
     */
    private $flatState;

    /**
     * @param Config $config
     * @param Processor $processor
     * @param FlatState $flatState
     */
    public function __construct(
        Config $config,
        Processor $processor,
        FlatState $flatState
    ) {
        $this->processor = $processor;
        $this->config = $config;
        $this->flatState = $flatState;
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
        if (!empty($productIds) && $this->processor->isIndexerScheduled() && $this->flatState->isFlatEnabled()) {
            $this->processor->markIndexerAsInvalid();
        }
    }
}
