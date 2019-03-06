<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\LegacyCatalogInventorySynchronization;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\Framework\Serialize\SerializerInterface;

class AsynchronousSetDataToLegacyCatalogInventoryConsumer
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var SynchronousSetDataToLegacyCatalogInventory
     */
    private $synchronousSetDataToLegacyCatalogInventory;

    /**
     * Consumer constructor.
     * @param SerializerInterface $serializer
     * @param SynchronousSetDataToLegacyCatalogInventory $synchronousSetDataToLegacyCatalogInventory
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        SerializerInterface $serializer,
        SynchronousSetDataToLegacyCatalogInventory $synchronousSetDataToLegacyCatalogInventory
    ) {
        $this->serializer = $serializer;
        $this->synchronousSetDataToLegacyCatalogInventory = $synchronousSetDataToLegacyCatalogInventory;
    }

    /**
     * Processing batch operations for legacy stock synchronization
     *
     * @param OperationInterface $operation
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function processOperations(OperationInterface $operation): void
    {
        $skus = $this->serializer->unserialize($operation->getSerializedData());
        $this->synchronousSetDataToLegacyCatalogInventory->execute($skus);
    }
}
