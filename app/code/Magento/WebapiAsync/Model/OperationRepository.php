<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\WebapiAsync\Model;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;
use Magento\AsynchronousOperations\Model\OperationRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Communication\ConfigInterface as CommunicationConfig;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\MessageQueue\MessageValidator;
use Magento\Framework\MessageQueue\MessageEncoder;
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
     * Kept so existing constructor preferences stay valid.
     *
     * @var MessageEncoder
     * @SuppressWarnings(PHPMD.UnusedPrivateField)
     */
    private $messageEncoder;

    /**
     * @var InputParamsResolver
     */
    private $inputParamsResolver;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CommunicationConfig
     */
    private $communicationConfig;

    /**
     * Initialize dependencies.
     *
     * @param OperationInterfaceFactory $operationFactory
     * @param EntityManager $entityManager
     * @param MessageValidator $messageValidator
     * @param Json $jsonSerializer
     * @param InputParamsResolver $inputParamsResolver
     * @param StoreManagerInterface|null $storeManager
     * @param MessageEncoder|null $messageEncoder
     * @param CommunicationConfig|null $communicationConfig
     */
    public function __construct(
        OperationInterfaceFactory $operationFactory,
        EntityManager $entityManager,
        MessageValidator $messageValidator,
        Json $jsonSerializer,
        InputParamsResolver $inputParamsResolver,
        ?StoreManagerInterface $storeManager = null,
        ?MessageEncoder $messageEncoder = null,
        ?CommunicationConfig $communicationConfig = null
    ) {
        $this->operationFactory = $operationFactory;
        $this->jsonSerializer = $jsonSerializer;
        $this->messageValidator = $messageValidator;
        $this->entityManager = $entityManager;
        $this->inputParamsResolver = $inputParamsResolver;
        $this->storeManager = $storeManager?: ObjectManager::getInstance()->get(StoreManagerInterface::class);
        $this->messageEncoder = $messageEncoder ?: ObjectManager::getInstance()->get(MessageEncoder::class);
        $this->communicationConfig = $communicationConfig
            ?: ObjectManager::getInstance()->get(CommunicationConfig::class);
    }

    /**
     * @inheritDoc
     */
    public function create($topicName, $entityParams, $groupId, $operationId): OperationInterface
    {
        $requestData = $this->inputParamsResolver->getInputData();
        if ($operationId === null || !isset($requestData[$operationId])) {
            throw new \InvalidArgumentException(
                'Parameter "$operationId" must not be NULL and must exist in input data'
            );
        }

        $this->messageValidator->validate($topicName, $entityParams);
        $operationPayload = $requestData[$operationId];
        if (is_array($operationPayload)) {
            $operationPayload = $this->normalizeTopLevelParameterNames($topicName, $operationPayload);
        }
        $encodedMessage = $this->jsonSerializer->serialize($operationPayload);

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

    /**
     * Rename only top-level keys to the topic method parameter names.
     *
     * Nested keys stay as submitted. The consumer already accepts snake_case and camelCase there.
     *
     * @param string $topicName
     * @param array $payload
     * @return array
     */
    private function normalizeTopLevelParameterNames(string $topicName, array $payload): array
    {
        $requestSchema = $this->communicationConfig->getTopic($topicName)[CommunicationConfig::TOPIC_REQUEST] ?? null;
        if (!is_array($requestSchema)) {
            return $payload;
        }

        foreach ($requestSchema as $parameter) {
            if (!is_array($parameter) || !isset($parameter[CommunicationConfig::SCHEMA_METHOD_PARAM_NAME])) {
                continue;
            }
            $paramName = $parameter[CommunicationConfig::SCHEMA_METHOD_PARAM_NAME];
            if (!is_string($paramName)) {
                continue;
            }
            $snakeCaseParamName = strtolower((string) preg_replace("/(?<=\\w)(?=[A-Z])/", "_$1", $paramName));
            if (!isset($payload[$paramName]) && isset($payload[$snakeCaseParamName])) {
                $payload[$paramName] = $payload[$snakeCaseParamName];
                unset($payload[$snakeCaseParamName]);
            }
        }

        return $payload;
    }
}
