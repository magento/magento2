<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\LegacyCatalogInventorySynchronization;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;
use Magento\Framework\Bulk\BulkManagementInterface;
use Magento\Framework\DataObject\IdentityGeneratorInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Set Qty and status for legacy CatalogInventory Stock Item table
 */
class AsynchronousSetDataToLegacyCatalogInventory
{
    private const TOPIC_NAME = 'inventory.catalog.product.legacy_inventory.set_data';

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
     * @var int
     */
    private $batchSize;

    /**
     * AsyncSetDataToLegacyCatalogInventory constructor.
     * @param BulkManagementInterface $bulkManagement
     * @param SerializerInterface $serializer
     * @param IdentityGeneratorInterface $identityService
     * @param OperationInterfaceFactory $operationInterfaceFactory
     * @param int $batchSize
     */
    public function __construct(
        BulkManagementInterface $bulkManagement,
        SerializerInterface $serializer,
        IdentityGeneratorInterface $identityService,
        OperationInterfaceFactory $operationInterfaceFactory,
        int $batchSize
    ) {
        $this->bulkManagement = $bulkManagement;
        $this->identityService = $identityService;
        $this->serializer = $serializer;
        $this->operationInterfaceFactory = $operationInterfaceFactory;
        $this->batchSize = $batchSize;
    }

    /**
     * @param array $skus
     * @return void
     */
    public function execute(array $skus): void
    {
        $operations = [];

        $bulkUuid = $this->identityService->generateId();

        $chunks = array_chunk($skus, $this->batchSize);
        foreach ($chunks as $chunk) {
            $data = [
                'data' => [
                    'bulk_uuid' => $bulkUuid,
                    'topic_name' => self::TOPIC_NAME,
                    'serialized_data' => $this->serializer->serialize($chunk),
                    'status' => OperationInterface::STATUS_TYPE_OPEN,
                ]
            ];

            /** @var \Magento\AsynchronousOperations\Api\Data\OperationInterface $operation */
            $operation = $this->operationInterfaceFactory->create($data);
            $operations[] = $operation;
        }

        $this->bulkManagement->scheduleBulk($bulkUuid, $operations, __('Set legacy stock data'));
    }
}
