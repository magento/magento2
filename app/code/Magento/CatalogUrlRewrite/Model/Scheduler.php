<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Model;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;
use Magento\Framework\Bulk\BulkManagementInterface;
use Magento\Framework\DataObject\IdentityGeneratorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;

class Scheduler
{
    private const TOPIC_NAME = 'catalog_product_generate_urls';

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
     * Schedule updating product url rewrites values.
     *
     * @param int $websiteId
     * @return void
     * @throws LocalizedException
     */
    public function execute(int $websiteId): void
    {
        $bulkUuid = $this->identityGenerator->generateId();
        $operation = $this->operationFactory->create(
            [
                'data' => [
                    'bulk_uuid' => $bulkUuid,
                    'topic_name' => self::TOPIC_NAME,
                    'serialized_data' => $this->serializer->serialize(['website_id' => $websiteId]),
                    'status' => OperationInterface::STATUS_TYPE_OPEN,
                ]
            ]
        );
        $bulkDescription = __('Update Product Url Rewrites values');
        $result = $this->bulkManagement->scheduleBulk($bulkUuid, [$operation], $bulkDescription);
        if (!$result) {
            throw new LocalizedException(__('Something went wrong while scheduling operations.'));
        }
    }
}
