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
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\App\ObjectManager;
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
     * @var \Magento\Framework\DataObject\IdentityGeneratorInterface
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
     * @var \Magento\Framework\Bulk\BulkManagementInterface
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
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * @var Encryptor
     */
    private $encryptor;

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
     * @param Encryptor|null $encryptor
     */
    public function __construct(
        IdentityGeneratorInterface $identityService,
        ItemStatusInterfaceFactory $itemStatusInterfaceFactory,
        AsyncResponseInterfaceFactory $asyncResponseFactory,
        BulkManagementInterface $bulkManagement,
        LoggerInterface $logger,
        OperationRepositoryInterface $operationRepository,
        UserContextInterface $userContext = null,
        Encryptor $encryptor = null
    ) {
        $this->identityService = $identityService;
        $this->itemStatusInterfaceFactory = $itemStatusInterfaceFactory;
        $this->asyncResponseFactory = $asyncResponseFactory;
        $this->bulkManagement = $bulkManagement;
        $this->logger = $logger;
        $this->operationRepository = $operationRepository;
        $this->userContext = $userContext ?: ObjectManager::getInstance()->get(UserContextInterface::class);
        $this->encryptor = $encryptor ?: ObjectManager::getInstance()->get(Encryptor::class);
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
            $asyncResponse->setErrors(true);
            $bulkException->addData($asyncResponse);
            throw $bulkException;
        } else {
            $asyncResponse->setErrors(false);
        }

        return $asyncResponse;
    }
}
