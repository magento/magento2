<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\SourceItemsSaveSynchronization;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;
use Magento\AsynchronousOperations\Model\MassSchedule;
use Magento\Framework\Bulk\BulkManagementInterface;
use Magento\Framework\DataObject\IdentityGeneratorInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Set Qty and status for legacy CatalogInventory Stock Item table
 */
class AsyncSetDataToLegacyCatalogInventory
{
    private const BATCH_SIZE = 100; // TODO: use di.xml to define the batch size
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
     * AsyncSetDataToLegacyCatalogInventory constructor.
     * @param BulkManagementInterface $bulkManagement
     * @param SerializerInterface $serializer
     * @param IdentityGeneratorInterface $identityService
     * @param OperationInterfaceFactory $operationInterfaceFactory
     */
    public function __construct(
        BulkManagementInterface $bulkManagement,
        SerializerInterface $serializer,
        IdentityGeneratorInterface $identityService,
        OperationInterfaceFactory $operationInterfaceFactory
    ) {
        $this->bulkManagement = $bulkManagement;
        $this->identityService = $identityService;
        $this->serializer = $serializer;
        $this->operationInterfaceFactory = $operationInterfaceFactory;
    }

    /**
     * @param array $skus
     * @return void
     */
    public function execute(array $skus): void
    {
        $operations = [];

        $bulkUuid = $this->identityService->generateId();

        $chunks = array_chunk($skus, self::BATCH_SIZE);
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
