<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\LegacyCatalogInventorySynchronization;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\InventoryCatalog\Model\LegacyCatalogInventorySynchronization\ToLegacyCatalogInventory\SetDataToLegacyInventory;

class AsyncConsumer
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var SetDataToLegacyInventory
     */
    private $setDataToLegacyInventory;

    /**
     * Consumer constructor.
     * @param SerializerInterface $serializer
     * @param SetDataToLegacyInventory $setDataToLegacyInventory
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        SerializerInterface $serializer,
        SetDataToLegacyInventory $setDataToLegacyInventory
    ) {
        $this->serializer = $serializer;
        $this->setDataToLegacyInventory = $setDataToLegacyInventory;
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
        $data = $this->serializer->unserialize($operation->getSerializedData());
        if ($data['direction'] === Synchronize::DIRECTION_TO_LEGACY) {
            $this->setDataToLegacyInventory->execute($data['skus']);
        }
    }
}
