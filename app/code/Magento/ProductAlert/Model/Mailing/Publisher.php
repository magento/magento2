<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ProductAlert\Model\Mailing;

use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;
use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Bulk\BulkManagementInterface;
use Magento\Framework\DataObject\IdentityGeneratorInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Publish Mailing of Product Alerts to messages queue
 */
class Publisher
{
    /**
     * Default value of bunch size for one operation
     */
    private const MESSAGE_BUNCH_SIZE_DEFAULT = 5000;

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
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var int|null
     */
    private $messageBunchSize;

    /**
     * @param BulkManagementInterface $bulkManagement
     * @param OperationInterfaceFactory $operationFactory
     * @param IdentityGeneratorInterface $identityService
     * @param UserContextInterface $userContextInterface
     * @param Json $jsonSerializer
     * @param int|null $messageBunchSize
     */
    public function __construct(
        BulkManagementInterface $bulkManagement,
        OperationInterfaceFactory $operationFactory,
        IdentityGeneratorInterface $identityService,
        UserContextInterface $userContextInterface,
        Json $jsonSerializer,
        ?int $messageBunchSize = null
    ) {
        $this->bulkManagement = $bulkManagement;
        $this->operationFactory = $operationFactory;
        $this->identityService = $identityService;
        $this->userContext = $userContextInterface;
        $this->jsonSerializer = $jsonSerializer;
        $this->messageBunchSize = $messageBunchSize ?: self::MESSAGE_BUNCH_SIZE_DEFAULT;
    }

    /**
     * Schedule bulk operation
     *
     * @param string $alertType
     * @param array $customerIds
     * @param int $websiteId
     */
    public function execute(string $alertType, array $customerIds, int $websiteId): void
    {
        foreach (array_chunk($customerIds, $this->messageBunchSize) as $bunchOfIds) {
            $bulkUuid = $this->identityService->generateId();
            $serializedData = $this->jsonSerializer->serialize(
                [
                    'alert_type' => $alertType,
                    'customer_ids' => $bunchOfIds,
                    'website_id' => $websiteId
                ]
            );
            /** @var OperationInterface $operation */
            $operation = $this->operationFactory->create(
                [
                    'data' => [
                        'bulk_uuid' => $bulkUuid,
                        'topic_name' => 'product_alert',
                        'serialized_data' => $serializedData,
                        'status' => OperationInterface::STATUS_TYPE_OPEN,
                    ]
                ]
            );
            $userId = $this->userContext->getUserId();
            $this->bulkManagement->scheduleBulk($bulkUuid, [$operation], __('Mailing of Product Alerts'), $userId);
        }
    }
}
