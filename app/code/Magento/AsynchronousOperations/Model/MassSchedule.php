<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Model;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;
use Magento\Framework\DataObject\IdentityGeneratorInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\AsynchronousOperations\Api\Data\ItemStatusInterfaceFactory;
use Magento\AsynchronousOperations\Api\Data\AsyncResponseInterface;
use Magento\AsynchronousOperations\Api\Data\AsyncResponseInterfaceFactory;
use Magento\AsynchronousOperations\Api\Data\ItemStatusInterface;
use Magento\Framework\MessageQueue\MessageValidator;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\Bulk\BulkManagementInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Exception\BulkException;
use Psr\Log\LoggerInterface;

/**
 * Class MassSchedule used for adding multiple entities as Operations to Bulk Management with the status tracking
 */
class MassSchedule
{
    /**
     * @var \Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory
     */
    private $operationFactory;

    /**
     * @var \Magento\Framework\DataObject\IdentityGeneratorInterface
     */
    private $identityService;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var AsyncResponseInterfaceFactory
     */
    private $asyncResponseFactory;

    /**
     * @var ItemStatusInterfaceFactory
     */
    private $itemStatusInterfaceFactory;

    /**
     * @var MessageEncoder
     */
    private $messageEncoder;

    /**
     * @var MessageValidator
     */
    private $messageValidator;

    /**
     * @var \Magento\Framework\Bulk\BulkManagementInterface
     */
    private $bulkManagement;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Initialize dependencies.
     *
     * @param OperationInterfaceFactory $operationFactory
     * @param IdentityGeneratorInterface $identityService
     * @param Json $jsonSerializer
     * @param EntityManager $entityManager
     * @param ItemStatusInterfaceFactory $itemStatusInterfaceFactory
     * @param AsyncResponseInterfaceFactory $asyncResponseFactory
     * @param MessageEncoder $messageEncoder
     * @param MessageValidator $messageValidator
     * @param BulkManagementInterface $bulkManagement
     * @param LoggerInterface $logger
     */
    public function __construct(
        OperationInterfaceFactory $operationFactory,
        IdentityGeneratorInterface $identityService,
        Json $jsonSerializer,
        EntityManager $entityManager,
        ItemStatusInterfaceFactory $itemStatusInterfaceFactory,
        AsyncResponseInterfaceFactory $asyncResponseFactory,
        MessageEncoder $messageEncoder,
        MessageValidator $messageValidator,
        BulkManagementInterface $bulkManagement,
        LoggerInterface $logger
    ) {
        $this->operationFactory = $operationFactory;
        $this->identityService = $identityService;
        $this->jsonSerializer = $jsonSerializer;
        $this->entityManager = $entityManager;
        $this->itemStatusInterfaceFactory = $itemStatusInterfaceFactory;
        $this->asyncResponseFactory = $asyncResponseFactory;
        $this->messageEncoder = $messageEncoder;
        $this->messageValidator = $messageValidator;
        $this->bulkManagement = $bulkManagement;
        $this->logger = $logger;
    }

    /**
     * Schedule new bulk operation based on the list of entities
     *
     * @param $topicName
     * @param $entitiesArray
     * @param null $groupId
     * @param null $userId
     * @return AsyncResponseInterface
     * @throws BulkException
     * @throws LocalizedException
     */
    public function publishMass($topicName, array $entitiesArray, $groupId = null, $userId = null)
    {
        $bulkDescription = __('Topic %s', $topicName);

        if ($groupId == null) {
            $groupId = $this->identityService->generateId();

            /** create new bulk without operations */
            if (!$this->bulkManagement->scheduleBulk($groupId, [], $bulkDescription, $userId)) {
                throw new LocalizedException(
                    __('Something went wrong while processing the request.')
                );
            }
        }

        $operations = [];
        $requestItems = [];
        $bulkException = new BulkException();
        foreach ($entitiesArray as $key => $entityParams) {
            /** @var \Magento\WebapiAsync\Api\Data\ItemStatusInterface $requestItem */
            $requestItem = $this->itemStatusInterfaceFactory->create();

            try {
                $this->messageValidator->validate($topicName, $entityParams);
                $encodedMessage = $this->messageEncoder->encode($topicName, $entityParams);

                $serializedData = [
                    'entity_id'        => null,
                    'entity_link'      => '',
                    'meta_information' => $encodedMessage,
                ];
                $data = [
                    'data' => [
                        OperationInterface::BULK_ID         => $groupId,
                        OperationInterface::TOPIC_NAME      => $topicName,
                        OperationInterface::SERIALIZED_DATA => $this->jsonSerializer->serialize($serializedData),
                        OperationInterface::STATUS          => OperationInterface::STATUS_TYPE_OPEN,
                    ],
                ];

                /** @var \Magento\AsynchronousOperations\Api\Data\OperationInterface $operation */
                $operation = $this->operationFactory->create($data);
                $operations[] = $this->entityManager->save($operation);
                $requestItem->setId($key);
                $requestItem->setStatus(ItemStatusInterface::STATUS_ACCEPTED);
                $requestItems[] = $requestItem;
            } catch (\Exception $exception) {
                $this->logger->error($exception);
                $requestItem->setId($key);
                $requestItem->setStatus(ItemStatusInterface::STATUS_REJECTED);
                $requestItem->setErrorMessage($exception);
                $requestItem->setErrorCode($exception);
                $requestItems[] = $requestItem;
                $bulkException->addException(new LocalizedException(
                    __('Error processing %key element of input data', ['key' => $key]),
                    $exception
                ));
            }
        }

        if (!$this->bulkManagement->scheduleBulk($groupId, $operations, $bulkDescription, $userId)) {
            throw new LocalizedException(
                __('Something went wrong while processing the request.')
            );
        }
        /** @var AsyncResponseInterface $asyncResponse */
        $asyncResponse = $this->asyncResponseFactory->create();
        $asyncResponse->setBulkUuid($groupId);
        $asyncResponse->setRequestItems($requestItems);

        if ($bulkException->wasErrorAdded()) {
            $asyncResponse->setIsErrors(true);
            $bulkException->addData($asyncResponse);
            throw $bulkException;
        } else {
            $asyncResponse->setIsErrors(false);
        }

        return $asyncResponse;
    }
}
