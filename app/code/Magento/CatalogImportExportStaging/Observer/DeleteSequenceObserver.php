<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExportStaging\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\CatalogStaging\Model\ResourceModel\ProductSequence\Collection;

class DeleteSequenceObserver implements ObserverInterface
{
    /**
     * @var Collection
     */
    private $productSequenceCollection;

    /**
     * Constructor
     *
     * @param Collection $productSequenceCollection
     */
    public function __construct(
        Collection $productSequenceCollection
    ) {
        $this->productSequenceCollection = $productSequenceCollection;
    }

    /**
     * Delete sequence
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @throws \LogicException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $ids = $observer->getIdsToDelete();
        $this->productSequenceCollection->deleteSequence($ids);
    }
}
