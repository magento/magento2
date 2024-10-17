<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model\Service;

use Magento\Framework\Bulk\BulkManagementInterface;
use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;
use Magento\Framework\DataObject\IdentityGeneratorInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Bulk\OperationInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\SalesRule\Model\Coupon\Usage\UpdateInfo;

/**
 * Scheduler for coupon usage queue
 */
class CouponUsagePublisher
{
    private const TOPIC_NAME = 'sales.rule.update.coupon.usage';

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
     * Publish sales rule usage info into the queue
     *
     * @param string $updateInfo
     * @return boolean
     */
    public function publish(UpdateInfo $updateInfo): bool
    {
        $bulkUuid = $this->identityService->generateId();
        $bulkDescription = __('Rule processing: %1', implode(',', $updateInfo->getAppliedRuleIds()));

        $data = [
            'data' => [
                'bulk_uuid' => $bulkUuid,
                'topic_name' => self::TOPIC_NAME,
                'serialized_data' => $this->serializer->serialize($updateInfo->getData()),
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
