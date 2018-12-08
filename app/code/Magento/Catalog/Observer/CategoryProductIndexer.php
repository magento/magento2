<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Observer;

use Magento\Catalog\Model\Indexer\Category\Product\Processor;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
<<<<<<< HEAD
 * Checks if a category has changed products and depends on indexer configuration
 * marks `Category Products` indexer as invalid or reindexes affected products.
=======
 * Checks if a category has changed products and depends on indexer configuration.
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
 */
class CategoryProductIndexer implements ObserverInterface
{
    /**
     * @var Processor
     */
    private $processor;

    /**
     * @param Processor $processor
     */
    public function __construct(Processor $processor)
    {
        $this->processor = $processor;
    }

    /**
     * @inheritdoc
     */
<<<<<<< HEAD
    public function execute(Observer $observer)
=======
    public function execute(Observer $observer): void
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
    {
        $productIds = $observer->getEvent()->getProductIds();
        if (!empty($productIds) && $this->processor->isIndexerScheduled()) {
            $this->processor->markIndexerAsInvalid();
        }
    }
}
