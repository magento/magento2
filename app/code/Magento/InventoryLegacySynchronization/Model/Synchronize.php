<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLegacySynchronization\Model;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;
use Magento\Framework\Bulk\BulkManagementInterface;
use Magento\Framework\DataObject\IdentityGeneratorInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Set Qty and status for legacy CatalogInventory Stock Item table
 */
class Synchronize
{
    /**
     * Asynchronous operation topic name
     */
    private const TOPIC_NAME = 'inventory.legacy_synchronization.set_data';

    /**
     * Synchronization from MSI source items to legacy catalog inventory
     */
    public const MSI_TO_LEGACY = 'synchronize-msi-to-legacy';

    /**
     * Synchronization from legacy catalog inventory to MSI source items
     */
    public const LEGACY_TO_MSI = 'synchronize-legacy-to-msi';

    /**
     * @var BulkManagementInterface
     */
    private $bulkManagement;

    /**
     * @var IdentityGeneratorInterface
     */
    private $identityService;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var OperationInterfaceFactory
     */
    private $operationInterfaceFactory;

    /**
     * @var IsAsyncLegacyAlignment
     */
    private $isAsyncLegacyAlignment;

    /**
     * @var SynchronizeInventoryData
     */
    private $synchronizeInventoryData;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @param BulkManagementInterface $bulkManagement
     * @param SerializerInterface $serializer
     * @param IdentityGeneratorInterface $identityService
     * @param OperationInterfaceFactory $operationInterfaceFactory
     * @param IsAsyncLegacyAlignment $isAsyncLegacyAlignment
     * @param SynchronizeInventoryData $synchronizeInventoryData
     * @param int $batchSize
     */
    public function __construct(
        BulkManagementInterface $bulkManagement,
        SerializerInterface $serializer,
        IdentityGeneratorInterface $identityService,
        OperationInterfaceFactory $operationInterfaceFactory,
        IsAsyncLegacyAlignment $isAsyncLegacyAlignment,
        SynchronizeInventoryData $synchronizeInventoryData,
        int $batchSize
    ) {
        $this->bulkManagement = $bulkManagement;
        $this->identityService = $identityService;
        $this->serializer = $serializer;
        $this->operationInterfaceFactory = $operationInterfaceFactory;
        $this->batchSize = $batchSize;
        $this->isAsyncLegacyAlignment = $isAsyncLegacyAlignment;
        $this->synchronizeInventoryData = $synchronizeInventoryData;
    }

    /**
     * @param string $destination
     * @param array $items
     */
    private function executeAsync(string $destination, array $items): void
    {
        $asyncOperations = [];

        $bulkUuid = $this->identityService->generateId();

        $chunks = array_chunk($items, $this->batchSize);
        foreach ($chunks as $chunk) {
            $data = [
                'data' => [
                    'bulk_uuid' => $bulkUuid,
                    'topic_name' => self::TOPIC_NAME,
                    'serialized_data' => $this->serializer->serialize(
                        [
                            'destination' => $destination,
                            'items' => $chunk
                        ]
                    ),
                    'status' => OperationInterface::STATUS_TYPE_OPEN,
                ]
            ];

            /** @var OperationInterface $asyncOperation */
            $asyncOperation = $this->operationInterfaceFactory->create($data);
            $asyncOperations[] = $asyncOperation;
        }

        $this->bulkManagement->scheduleBulk($bulkUuid, $asyncOperations, __('Synchronize legacy stock'));
    }

    /**
     * @param string $destination
     * @param array $items
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function executeSync(string $destination, array $items): void
    {
        $this->synchronizeInventoryData->execute($destination, $items);
    }

    /**
     * @param string $destination
     * @param array $items
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(string $destination, array $items): void
    {
        if (empty($items)) {
            return;
        }

        if ($this->isAsyncLegacyAlignment->execute()) {
            $this->executeAsync($destination, $items);
        } else {
            $this->executeSync($destination, $items);
        }
    }
}
