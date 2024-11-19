<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\WebapiAsync\Model;

use InvalidArgumentException;
use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;
use Magento\AsynchronousOperations\Model\OperationRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\MessageQueue\MessageValidator;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\WebapiAsync\Controller\Rest\Asynchronous\InputParamsResolver;

/**
 * Repository class to create operation
 */
class OperationRepository implements OperationRepositoryInterface
{
    /**
     * Initialize dependencies.
     *
     * @param OperationInterfaceFactory $operationFactory
     * @param EntityManager $entityManager
     * @param MessageValidator $messageValidator
     * @param Json $jsonSerializer
     * @param InputParamsResolver $inputParamsResolver
     * @param StoreManagerInterface|null $storeManager
     */
    public function __construct(
        private readonly OperationInterfaceFactory $operationFactory,
        private readonly EntityManager $entityManager,
        private readonly MessageValidator $messageValidator,
        private readonly Json $jsonSerializer,
        private readonly InputParamsResolver $inputParamsResolver,
        private ?StoreManagerInterface $storeManager = null
    ) {
        $this->storeManager = $storeManager?: ObjectManager::getInstance()->get(StoreManagerInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function create($topicName, $entityParams, $groupId, $operationId): OperationInterface
    {

        $this->messageValidator->validate($topicName, $entityParams);
        $requestData = $this->inputParamsResolver->getInputData();
        if ($operationId === null || !isset($requestData[$operationId])) {
            throw new InvalidArgumentException(
                'Parameter "$operationId" must not be NULL and must exist in input data'
            );
        }
        $encodedMessage = $this->jsonSerializer->serialize($requestData[$operationId]);

        $serializedData = [
            'entity_id'        => null,
            'entity_link'      => '',
            'meta_information' => $encodedMessage,
        ];

        try {
            $storeId = $this->storeManager->getStore()->getId();
            $serializedData['store_id'] = $storeId;
            // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
        } catch (NoSuchEntityException $e) {
            // skip setting store id in the serialized data if store doesn't exist
        }

        $data = [
            'data' => [
                OperationInterface::ID => $operationId,
                OperationInterface::BULK_ID => $groupId,
                OperationInterface::TOPIC_NAME => $topicName,
                OperationInterface::SERIALIZED_DATA => $this->jsonSerializer->serialize($serializedData),
                OperationInterface::STATUS => OperationInterface::STATUS_TYPE_OPEN,
            ],
        ];
        /** @var OperationInterface $operation */
        $operation = $this->operationFactory->create($data);
        return $operation;
    }
}
