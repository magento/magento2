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
 * Checks if a category has changed products and depends on indexer configuration.
=======
 * Checks if a category has changed products and depends on indexer configuration
 * marks `Category Products` indexer as invalid or reindexes affected products.
>>>>>>> upstream/2.2-develop
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
    public function execute(Observer $observer): void
=======
    public function execute(Observer $observer)
>>>>>>> upstream/2.2-develop
    {
        $productIds = $observer->getEvent()->getProductIds();
        if (!empty($productIds) && $this->processor->isIndexerScheduled()) {
            $this->processor->markIndexerAsInvalid();
        }
    }
}
