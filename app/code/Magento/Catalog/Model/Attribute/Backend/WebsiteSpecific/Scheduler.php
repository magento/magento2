<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Attribute\Backend\WebsiteSpecific;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;
use Magento\Framework\Bulk\BulkManagementInterface;
use Magento\Framework\DataObject\IdentityGeneratorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;

class Scheduler
{
    private const TOPIC_NAME = 'catalog_website_attribute_value_sync';

    /**
     * @param BulkManagementInterface $bulkManagement
     * @param IdentityGeneratorInterface $identityGenerator
     * @param OperationInterfaceFactory $operationFactory
     * @param SerializerInterface $serializer
     */
    public function __construct(
        private BulkManagementInterface $bulkManagement,
        private IdentityGeneratorInterface $identityGenerator,
        private OperationInterfaceFactory $operationFactory,
        private SerializerInterface $serializer
    ) {
    }

    /**
     * Schedule website specific values synchronization.
     *
     * @param int $storeId
     * @return void
     * @throws LocalizedException
     */
    public function execute(int $storeId): void
    {
        $bulkUuid = $this->identityGenerator->generateId();
        $operation = $this->operationFactory->create(
            [
                'data' => [
                    'bulk_uuid' => $bulkUuid,
                    'topic_name' => self::TOPIC_NAME,
                    'serialized_data' => $this->serializer->serialize(['store_id' => $storeId]),
                    'status' => OperationInterface::STATUS_TYPE_OPEN,
                ]
            ]
        );
        $bulkDescription = __('Synchronize website specific attributes values');
        $result = $this->bulkManagement->scheduleBulk($bulkUuid, [$operation], $bulkDescription);
        if (!$result) {
            throw new LocalizedException(__('Something went wrong while scheduling operations.'));
        }
    }
}
