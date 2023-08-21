<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Observer;

use Magento\Catalog\Model\Indexer\Category\Product\Processor;
use Magento\Catalog\Model\Indexer\Category\Flat\State as FlatState;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Checks if a category has changed products and depends on indexer configuration.
 */
class CategoryProductIndexer implements ObserverInterface
{
    /**
     * @var Processor
     */
    private $processor;

    /**
     * @var FlatState
     */
    private $flatState;

    /**
     * @param Processor $processor
     * @param FlatState $flatState
     */
    public function __construct(
        Processor $processor,
        FlatState $flatState
    ) {
        $this->processor = $processor;
        $this->flatState = $flatState;
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer): void
    {
        $productIds = $observer->getEvent()->getProductIds();
        if (!empty($productIds) && $this->processor->isIndexerScheduled() && $this->flatState->isFlatEnabled()) {
            $this->processor->markIndexerAsInvalid();
        }
    }
}
