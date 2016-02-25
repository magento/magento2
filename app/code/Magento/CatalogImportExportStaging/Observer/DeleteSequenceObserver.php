<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExportStaging\Observer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Entity\MetadataPool;
use Magento\Framework\Model\Entity\SequenceRegistry;
use Magento\Framework\Phrase;

class DeleteSequenceObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var SequenceRegistry
     */
    private $sequenceRegistry;

    /**
     * Constructor
     *
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resource
     * @param SequenceRegistry $sequenceRegistry
     */
    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resource,
        SequenceRegistry $sequenceRegistry
    ) {
        $this->metadataPool = $metadataPool;
        $this->resource = $resource;
        $this->sequenceRegistry = $sequenceRegistry;
    }

    /**
     * Delete sequence
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @throws LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $ids = $observer->getIdsToDelete();
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $sequenceInfo = $this->sequenceRegistry->retrieve(ProductInterface::class);
        if (!isset($sequenceInfo['sequenceTable'])) {
            throw new LocalizedException(__('Sequence table doesn\'t exist'));
        }

        $metadata->getEntityConnection()->delete(
            $this->resource->getTableName($sequenceInfo['sequenceTable']),
            ['sequence_value IN (?)' => $ids]
        );
    }
}
