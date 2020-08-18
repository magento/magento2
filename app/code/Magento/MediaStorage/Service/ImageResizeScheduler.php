<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaStorage\Service;

use Magento\Framework\Bulk\BulkManagementInterface;
use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;
use Magento\Framework\DataObject\IdentityGeneratorInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Bulk\OperationInterface;
use Magento\Authorization\Model\UserContextInterface;

/**
 * Scheduler for image resize queue
 */
class ImageResizeScheduler
{
    /**
     * @var BulkManagementInterface
     */
    private $bulkManagement;

    /**
     * @var OperationInterfaceFactory
     */
    private $operationFactory;

    /**
     * @var IdentityGeneratorInterface
     */
    private $identityService;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @param BulkManagementInterface $bulkManagement
     * @param OperationInterfaceFactory $operartionFactory
     * @param IdentityGeneratorInterface $identityService
     * @param SerializerInterface $serializer
     * @param UserContextInterface $userContext
     */
    public function __construct(
        BulkManagementInterface $bulkManagement,
        OperationInterfaceFactory $operartionFactory,
        IdentityGeneratorInterface $identityService,
        SerializerInterface $serializer,
        UserContextInterface $userContext
    ) {
        $this->bulkManagement = $bulkManagement;
        $this->operationFactory = $operartionFactory;
        $this->identityService = $identityService;
        $this->serializer = $serializer;
        $this->userContext = $userContext;
    }

    /**
     * Schedule image resize based on original image.
     *
     * @param string $imageName
     * @return boolean
     */
    public function schedule(string $imageName): bool
    {
        $bulkUuid = $this->identityService->generateId();
        $bulkDescription = __('Image resize: %1', $imageName);
        $dataToEncode = ['filename' => $imageName];

        $data = [
            'data' => [
                'bulk_uuid' => $bulkUuid,
                'topic_name' => 'media.storage.catalog.image.resize',
                'serialized_data' => $this->serializer->serialize($dataToEncode),
                'status' => OperationInterface::STATUS_TYPE_OPEN,
            ]
        ];
        $operation = $this->operationFactory->create($data);

        return $this->bulkManagement->scheduleBulk(
            $bulkUuid,
            [$operation],
            $bulkDescription,
            $this->userContext->getUserId()
        );
    }
}
