<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SalesRule\Model\Rule;

use Magento\Authorization\Model\UserContextInterface;
use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;
use Magento\Framework\Bulk\BulkManagementInterface;
use Magento\Framework\Bulk\OperationInterface;
use Magento\Framework\DataObject\IdentityGeneratorInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\SalesRule\Model\Spi\RuleQuoteRecollectTotalsInterface;

/**
 * Trigger recollect totals for quotes asynchronously.
 */
class RuleQuoteRecollectTotalsAsync implements RuleQuoteRecollectTotalsInterface
{
    private const TOPIC_NAME = 'sales.rule.quote.trigger.recollect';

    /**
     * @param BulkManagementInterface $bulkManagement
     * @param OperationInterfaceFactory $operationFactory
     * @param IdentityGeneratorInterface $identityService
     * @param SerializerInterface $serializer
     * @param UserContextInterface $userContext
     */
    public function __construct(
        private readonly BulkManagementInterface $bulkManagement,
        private readonly OperationInterfaceFactory $operationFactory,
        private readonly IdentityGeneratorInterface $identityService,
        private readonly SerializerInterface $serializer,
        private readonly UserContextInterface $userContext
    ) {
    }

    /**
     * Publish a message in the queue for triggering recollect totals for quotes affected by rule ID
     *
     * @param int $ruleId
     * @return void
     */
    public function execute(int $ruleId): void
    {
        $bulkUuid = $this->identityService->generateId();
        $bulkDescription = __('Trigger recollect totals for quotes by rule ID %1', $ruleId);

        $data = [
            'data' => [
                'bulk_uuid' => $bulkUuid,
                'topic_name' => self::TOPIC_NAME,
                'serialized_data' => $this->serializer->serialize(['rule_id' => $ruleId]),
                'status' => OperationInterface::STATUS_TYPE_OPEN,
            ]
        ];
        $operation = $this->operationFactory->create($data);

        $this->bulkManagement->scheduleBulk(
            $bulkUuid,
            [$operation],
            $bulkDescription,
            $this->userContext->getUserId()
        );
    }
}
