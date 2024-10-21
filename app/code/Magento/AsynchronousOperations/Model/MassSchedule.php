<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model;

use Magento\AsynchronousOperations\Api\Data\AsyncResponseInterface;
use Magento\AsynchronousOperations\Api\Data\AsyncResponseInterfaceFactory;
use Magento\AsynchronousOperations\Api\Data\ItemStatusInterface;
use Magento\AsynchronousOperations\Api\Data\ItemStatusInterfaceFactory;
use Magento\AsynchronousOperations\Api\SaveMultipleOperationsInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Bulk\BulkManagementInterface;
use Magento\Framework\DataObject\IdentityGeneratorInterface;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Exception\BulkException;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * Class MassSchedule used for adding multiple entities as Operations to Bulk Management with the status tracking
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Suppressed without refactoring to not introduce BiC
 */
class MassSchedule
{
    /**
     * @var IdentityGeneratorInterface
     */
    private $identityService;

    /**
     * @var AsyncResponseInterfaceFactory
     */
    private $asyncResponseFactory;

    /**
     * @var ItemStatusInterfaceFactory
     */
    private $itemStatusInterfaceFactory;

    /**
     * @var BulkManagementInterface
     */
    private $bulkManagement;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OperationRepositoryInterface
     */
    private $operationRepository;

    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var Encryptor
     */
    private $encryptor;

    /**
     * @var SaveMultipleOperationsInterface
     */
    private $saveMultipleOperations;

    /**
     * Initialize dependencies.
     *
     * @param IdentityGeneratorInterface $identityService
     * @param ItemStatusInterfaceFactory $itemStatusInterfaceFactory
     * @param AsyncResponseInterfaceFactory $asyncResponseFactory
     * @param BulkManagementInterface $bulkManagement
     * @param LoggerInterface $logger
     * @param OperationRepositoryInterface $operationRepository
     * @param UserContextInterface $userContext
     * @param Encryptor $encryptor
     * @param SaveMultipleOperationsInterface $saveMultipleOperations
     */
    public function __construct(
        IdentityGeneratorInterface $identityService,
        ItemStatusInterfaceFactory $itemStatusInterfaceFactory,
        AsyncResponseInterfaceFactory $asyncResponseFactory,
        BulkManagementInterface $bulkManagement,
        LoggerInterface $logger,
        OperationRepositoryInterface $operationRepository,
        UserContextInterface $userContext,
        Encryptor $encryptor,
        SaveMultipleOperationsInterface $saveMultipleOperations
    ) {
        $this->identityService = $identityService;
        $this->itemStatusInterfaceFactory = $itemStatusInterfaceFactory;
        $this->asyncResponseFactory = $asyncResponseFactory;
        $this->bulkManagement = $bulkManagement;
        $this->logger = $logger;
        $this->operationRepository = $operationRepository;
        $this->userContext = $userContext;
        $this->encryptor = $encryptor;
        $this->saveMultipleOperations = $saveMultipleOperations;
    }

    /**
     * Schedule new bulk operation based on the list of entities
     *
     * @param string $topicName
     * @param array $entitiesArray
     * @param string $groupId
     * @param string $userId
     * @return AsyncResponseInterface
     * @throws BulkException
     * @throws LocalizedException
     */
    public function publishMass($topicName, array $entitiesArray, $groupId = null, $userId = null)
    {
        $bulkDescription = __('Topic %1', $topicName);

        if ($userId == null) {
            $userId = $this->userContext->getUserId();
        }

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
            /** @var \Magento\AsynchronousOperations\Api\Data\ItemStatusInterface $requestItem */
            $requestItem = $this->itemStatusInterfaceFactory->create();
            try {
                $operation = $this->operationRepository->create($topicName, $entityParams, $groupId, $key);
                $operations[] = $operation;
                $requestItem->setId($key);
                $requestItem->setStatus(ItemStatusInterface::STATUS_ACCEPTED);
                $requestItem->setDataHash(
                    $this->encryptor->hash($operation->getSerializedData(), Encryptor::HASH_VERSION_SHA256)
                );
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

        try {
            $this->saveMultipleOperations->execute($operations);
        } catch (\Exception $exception) {
            $this->cleanupAndError($groupId);
        }
        if (!$this->bulkManagement->scheduleBulk($groupId, $operations, $bulkDescription, $userId)) {
            $this->cleanupAndError($groupId);
        }

        /** @var AsyncResponseInterface $asyncResponse */
        $asyncResponse = $this->asyncResponseFactory->create();
        $asyncResponse->setBulkUuid($groupId);
        $asyncResponse->setRequestItems($requestItems);

        if ($bulkException->wasErrorAdded()) {
            $asyncResponse->setErrors(true);
            $bulkException->addData($asyncResponse);
            throw $bulkException;
        } else {
            $asyncResponse->setErrors(false);
        }

        return $asyncResponse;
    }

    /**
     * Delete bulk, all related operations and throw error
     *
     * @param string $groupId
     * @return void
     * @throws LocalizedException
     */
    private function cleanupAndError($groupId): void
    {
        try {
            $this->bulkManagement->deleteBulk($groupId);
        } finally {
            throw new LocalizedException(
                __('Something went wrong while processing the request.')
            );
        }
    }
}
