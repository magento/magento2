<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\WebapiAsync\Model;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;
use Magento\AsynchronousOperations\Model\ConfigInterface as WebApiAsyncConfig;
use Magento\AsynchronousOperations\Model\OperationRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\MessageQueue\MessageValidator;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\WebapiAsync\Controller\Rest\Asynchronous\InputParamsResolver;
use Magento\Framework\AuthorizationInterface;

/**
 * Repository class to create operation
 */
class OperationRepository implements OperationRepositoryInterface
{

    public const CUSTOMER_CREATE_RESOURCE = 'Magento_Customer::create';

    /**
     * @var OperationInterfaceFactory
     */
    private $operationFactory;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var MessageValidator
     */
    private $messageValidator;
    /**
     * @var InputParamsResolver
     */
    private $inputParamsResolver;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var WebApiAsyncConfig
     */
    private $webapiAsyncConfig;

    /**
     * Initialize dependencies.
     *
     * @param OperationInterfaceFactory $operationFactory
     * @param EntityManager $entityManager
     * @param MessageValidator $messageValidator
     * @param Json $jsonSerializer
     * @param InputParamsResolver $inputParamsResolver
     * @param StoreManagerInterface|null $storeManager
     * @param AuthorizationInterface|null $authorization
     * @param WebApiAsyncConfig|null $webapiAsyncConfig
     */
    public function __construct(
        OperationInterfaceFactory $operationFactory,
        EntityManager $entityManager,
        MessageValidator $messageValidator,
        Json $jsonSerializer,
        InputParamsResolver $inputParamsResolver,
        StoreManagerInterface $storeManager = null,
        AuthorizationInterface $authorization = null,
        WebApiAsyncConfig $webapiAsyncConfig = null,
    ) {
        $this->operationFactory = $operationFactory;
        $this->jsonSerializer = $jsonSerializer;
        $this->messageValidator = $messageValidator;
        $this->entityManager = $entityManager;
        $this->inputParamsResolver = $inputParamsResolver;
        $this->storeManager = $storeManager?: ObjectManager::getInstance()->get(StoreManagerInterface::class);
        $this->authorization = $authorization ?? ObjectManager::getInstance()->get(AuthorizationInterface::class);
        $this->webapiAsyncConfig = $webapiAsyncConfig ?? ObjectManager::getInstance()->get(WebApiAsyncConfig::class);
    }

    /**
     * @inheritDoc
     */
    public function create($topicName, $entityParams, $groupId, $operationId): OperationInterface
    {

        $this->messageValidator->validate($topicName, $entityParams);
        $requestData = $this->inputParamsResolver->getInputData();
        if ($operationId === null || !isset($requestData[$operationId])) {
            throw new \InvalidArgumentException(
                'Parameter "$operationId" must not be NULL and must exist in input data'
            );
        }
        $encodedMessage = $this->jsonSerializer->serialize($requestData[$operationId]);

        $serializedData = [
            'entity_id'        => null,
            'entity_link'      => '',
            'meta_information' => $encodedMessage,
        ];

        if ($topicName === $this->webapiAsyncConfig->getTopicName('V1/customers', 'POST') &&
            $this->authorization->isAllowed(static::CUSTOMER_CREATE_RESOURCE)) {
            //custom attribute to validate operation request
            $serializedData['request_authorized'] = 1;
        }

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
