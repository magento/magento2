<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\LegacySynchronization;

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
    private const TOPIC_NAME = 'inventory.catalog.product.legacy_inventory.set_data';

    public const DIRECTION_TO_LEGACY = 'to-legacy';
    public const DIRECTION_TO_INVENTORY = 'to-inventory';

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
     * @var SetDataToDestination
     */
    private $setDataToDestination;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * AsyncSetDataToLegacyCatalogInventory constructor.
     * @param BulkManagementInterface $bulkManagement
     * @param SerializerInterface $serializer
     * @param IdentityGeneratorInterface $identityService
     * @param OperationInterfaceFactory $operationInterfaceFactory
     * @param IsAsyncLegacyAlignment $isAsyncLegacyAlignment
     * @param SetDataToDestination $setDataToDestination
     * @param int $batchSize
     */
    public function __construct(
        BulkManagementInterface $bulkManagement,
        SerializerInterface $serializer,
        IdentityGeneratorInterface $identityService,
        OperationInterfaceFactory $operationInterfaceFactory,
        IsAsyncLegacyAlignment $isAsyncLegacyAlignment,
        SetDataToDestination $setDataToDestination,
        int $batchSize
    ) {
        $this->bulkManagement = $bulkManagement;
        $this->identityService = $identityService;
        $this->serializer = $serializer;
        $this->operationInterfaceFactory = $operationInterfaceFactory;
        $this->batchSize = $batchSize;
        $this->isAsyncLegacyAlignment = $isAsyncLegacyAlignment;
        $this->setDataToDestination = $setDataToDestination;
    }

    /**
     * @param string $direction
     * @param array $items
     */
    private function executeAsync(string $direction, array $items): void
    {
        $operations = [];

        $bulkUuid = $this->identityService->generateId();

        $chunks = array_chunk($items, $this->batchSize);
        foreach ($chunks as $chunk) {
            $data = [
                'data' => [
                    'bulk_uuid' => $bulkUuid,
                    'topic_name' => self::TOPIC_NAME,
                    'serialized_data' => $this->serializer->serialize(
                        [
                            'direction' => $direction,
                            'items' => $chunk
                        ]
                    ),
                    'status' => OperationInterface::STATUS_TYPE_OPEN,
                ]
            ];

            /** @var \Magento\AsynchronousOperations\Api\Data\OperationInterface $operation */
            $operation = $this->operationInterfaceFactory->create($data);
            $operations[] = $operation;
        }

        $this->bulkManagement->scheduleBulk($bulkUuid, $operations, __('Synchronize legacy stock'));
    }

    /**
     * @param string $direction
     * @param array $items
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function executeSync(string $direction, array $items): void
    {
        $this->setDataToDestination->execute($direction, $items);
    }

    /**
     * @param string $direction
     * @param array $items
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(string $direction, array $items): void
    {
        if ($this->isAsyncLegacyAlignment->execute()) {
            $this->executeAsync($direction, $items);
        } else {
            $this->executeSync($direction, $items);
        }
    }
}
