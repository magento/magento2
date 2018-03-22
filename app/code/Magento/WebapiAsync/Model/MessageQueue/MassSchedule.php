<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\WebapiAsync\Model\MessageQueue;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;
use Magento\Framework\DataObject\IdentityGeneratorInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\WebapiAsync\Api\Data\AsyncResponseInterfaceFactory;
use Magento\WebapiAsync\Api\Data\AsyncResponse\ItemStatusInterfaceFactory;
use Magento\WebapiAsync\Api\Data\AsyncResponse\ItemsListInterfaceFactory;
use Magento\WebapiAsync\Api\Data\AsyncResponse\ItemStatusInterface;
use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterfaceFactory;
use Magento\Framework\MessageQueue\MessageValidator;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\Bulk\BulkManagementInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class MassPublisher used for encoding topic entities to OperationInterface and publish them.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $jsonHelper;

    /**
     * @var \Magento\Framework\EntityManager\EntityManager
     */
    private $entityManager;

    /**
     * @var \Magento\WebapiAsync\Api\Data\AsyncResponseInterfaceFactory
     */
    private $asyncResponseFactory;

    /**
     * @var \Magento\WebapiAsync\Api\Data\AsyncResponse\ItemsListInterfaceFactory
     */
    private $itemsListInterfaceFactory;

    /**
     * @var \Magento\WebapiAsync\Api\Data\AsyncResponse\ItemStatusInterfaceFactory
     */
    private $itemStatusInterfaceFactory;

    /**
     * @var BulkSummaryInterfaceFactory
     */
    private $bulkSummaryFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

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
     * Initialize dependencies.
     *
     * @param \Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory $operationFactory
     * @param \Magento\Framework\DataObject\IdentityGeneratorInterface $identityService
     * @param \Magento\Authorization\Model\UserContextInterface $userContextInterface
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonHelper
     * @param \Magento\Framework\EntityManager\EntityManager $entityManager
     * @param \Magento\WebapiAsync\Api\Data\AsyncResponseInterfaceFactory $asyncResponse
     * @param \Magento\WebapiAsync\Api\Data\AsyncResponse\ItemsListInterfaceFactory $itemsListFactory
     * @param \Magento\WebapiAsync\Api\Data\AsyncResponse\ItemStatusInterfaceFactory $itemStatusFactory
     * @param \Magento\AsynchronousOperations\Api\Data\BulkSummaryInterfaceFactory $bulkSummaryFactory
     * @param \Magento\Framework\MessageQueue\MessageEncoder $messageEncoder
     * @param \Magento\Framework\MessageQueue\MessageValidator $messageValidator
     * @param \Magento\Framework\Bulk\BulkManagementInterface $bulkManagement
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        OperationInterfaceFactory $operationFactory,
        IdentityGeneratorInterface $identityService,
        UserContextInterface $userContextInterface,
        Json $jsonHelper,
        EntityManager $entityManager,
        AsyncResponseInterfaceFactory $asyncResponse,
        ItemsListInterfaceFactory $itemsListFactory,
        ItemStatusInterfaceFactory $itemStatusFactory,
        BulkSummaryInterfaceFactory $bulkSummaryFactory,
        MessageEncoder $messageEncoder,
        MessageValidator $messageValidator,
        BulkManagementInterface $bulkManagement,
        LoggerInterface $logger
    ) {
        $this->userContext = $userContextInterface;
        $this->operationFactory = $operationFactory;
        $this->identityService = $identityService;
        $this->jsonHelper = $jsonHelper;
        $this->entityManager = $entityManager;
        $this->asyncResponseFactory = $asyncResponse;
        $this->itemsListInterfaceFactory = $itemsListFactory;
        $this->itemStatusInterfaceFactory = $itemStatusFactory;
        $this->bulkSummaryFactory = $bulkSummaryFactory;
        $this->messageEncoder = $messageEncoder;
        $this->messageValidator = $messageValidator;
        $this->bulkManagement = $bulkManagement;

        $this->logger = $logger ? : \Magento\Framework\App\ObjectManager::getInstance()->get(LoggerInterface::class);
    }

    /**
     * Schedule new bulk operation
     *
     * @param string $topicName
     * @param array $entitiesArray
     * @param null|string $groupId
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\WebapiAsync\Api\Data\AsyncResponseInterface
     */
    public function publishMass($topicName, $entitiesArray, $groupId = null)
    {
        $bulkDescription = sprintf('Topic %s', $topicName);
        $userId = $this->userContext->getUserId();

        /**
         * set admin userId to 1 because seems it's not work with oAuth
         * and we need set user id manually
         */
        if (!isset($userId) || $userId == 0) {
            $userId = 1;
        }

        if ($groupId == null) {
            $groupId = $this->identityService->generateId();

            /** create new bulk without operations */
            $this->bulkManagement->scheduleBulk($groupId, [], $bulkDescription, $userId);
        }
        /** @var \Magento\WebapiAsync\Api\Data\AsyncResponseInterface $asyncResponse */
        $asyncResponse = $this->asyncResponseFactory->create();
        $asyncResponse->setBulkUuid($groupId);

        $operations = [];
        $requestItems = [];
        foreach ($entitiesArray as $entityParams) {
            /** @var \Magento\WebapiAsync\Api\Data\AsyncResponse\ItemStatusInterface $requestItem */
            $requestItem = $this->itemStatusInterfaceFactory->create();

            try {
                $this->messageValidator->validate($topicName, $entityParams);
                $data = $this->messageEncoder->encode($topicName, $entityParams);
                $operationStatus = OperationInterface::STATUS_TYPE_OPEN;
            } catch (\Exception $exception) {
                $data = $entityParams;
                //TODO after merge with BulkApi Status need change cons from OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED to OperationInterface::STATUS_TYPE_REJECTED
                $operationStatus = OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED;
            }
            if (!isset($exception)) {
                $operation = $this->saveOperation($groupId, $topicName, $data, $operationStatus);
            } else {
                $operation =
                    $this->saveOperation(
                        $groupId,
                        $topicName,
                        $data,
                        $operationStatus,
                        $exception->getMessage(),
                        $exception->getCode()
                    );
            }
            if (!isset($exception)) {
                $operations[] = $operation;
            }

            $requestItem->setId($operation->getId());
            $requestItem->setStatus(ItemStatusInterface::STATUS_ACCEPTED);

            if (isset($exception)) {
                $requestItem->setStatus(ItemStatusInterface::STATUS_REJECTED);
                $requestItem->setErrorMessage($exception);
                $requestItem->setErrorCode($exception);
                unset($exception);
            }

            $requestItems[] = $requestItem;
        }

        $result = $this->bulkManagement->scheduleBulk($groupId, $operations, $bulkDescription, $userId);

        if (!$result) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Something went wrong while processing the request.')
            );
        }

        /** @var \Magento\WebapiAsync\Api\Data\AsyncResponse\ItemsListInterface $itemsResponseList */
        $requestItemsList = $this->itemsListInterfaceFactory->create(['items' => $requestItems]);
        $asyncResponse->setRequestItems($requestItemsList);

        return $asyncResponse;
    }

    /**
     * @param string $groupId
     * @param string $topicName
     * @param mixed $data
     * @param int $operationStatus
     * @param null $error
     * @param null $errorCode
     * @return \Magento\AsynchronousOperations\Api\Data\OperationInterface
     */
    private function saveOperation(
        $groupId,
        $topicName,
        $data,
        $operationStatus = OperationInterface::STATUS_TYPE_OPEN,
        $error = null,
        $errorCode = null
    ) {
        $serializedData = [
            'entity_id'        => null,
            'entity_link'      => '',
            'meta_information' => $data,
        ];
        $data = [
            'data' => [
                OperationInterface::BULK_ID         => $groupId,
                OperationInterface::TOPIC_NAME      => $topicName,
                OperationInterface::SERIALIZED_DATA => $this->jsonHelper->serialize($serializedData),
                OperationInterface::STATUS          => $operationStatus,
                OperationInterface::RESULT_MESSAGE  => $error,
                OperationInterface::ERROR_CODE      => $errorCode,
            ],
        ];

        /** @var \Magento\AsynchronousOperations\Api\Data\OperationInterface $operation */
        $operation = $this->operationFactory->create($data);

        return $this->entityManager->save($operation);
    }
}
