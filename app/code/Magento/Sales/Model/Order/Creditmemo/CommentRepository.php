<?php
/************************************************************************
 * Copyright 2025 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ***********************************************************************
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Order\Creditmemo;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Sales\Api\CreditmemoCommentRepositoryInterface;
use Magento\Sales\Api\Data\CreditmemoCommentInterface;
use Magento\Sales\Api\Data\CreditmemoCommentInterfaceFactory;
use Magento\Sales\Api\Data\CreditmemoCommentSearchResultInterfaceFactory;
use Magento\Sales\Model\Spi\CreditmemoCommentResourceInterface;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoCommentSender;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CommentRepository implements CreditmemoCommentRepositoryInterface
{
    /**
     * @var CreditmemoCommentResourceInterface
     */
    private $commentResource;

    /**
     * @var CreditmemoCommentInterfaceFactory
     */
    private $commentFactory;

    /**
     * @var CreditmemoCommentSearchResultInterfaceFactory
     */
    private $searchResultFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var CreditmemoCommentSender
     */
    private $creditmemoCommentSender;

    /**
     * @var CreditmemoRepositoryInterface
     */
    private $creditmemoRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param CreditmemoCommentResourceInterface $commentResource
     * @param CreditmemoCommentInterfaceFactory $commentFactory
     * @param CreditmemoCommentSearchResultInterfaceFactory $searchResultFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param CreditmemoCommentSender|null $creditmemoCommentSender
     * @param CreditmemoRepositoryInterface|null $creditmemoRepository
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        CreditmemoCommentResourceInterface $commentResource,
        CreditmemoCommentInterfaceFactory $commentFactory,
        CreditmemoCommentSearchResultInterfaceFactory $searchResultFactory,
        CollectionProcessorInterface $collectionProcessor,
        ?CreditmemoCommentSender $creditmemoCommentSender = null,
        ?CreditmemoRepositoryInterface $creditmemoRepository = null,
        ?LoggerInterface $logger = null
    ) {
        $this->commentResource = $commentResource;
        $this->commentFactory = $commentFactory;
        $this->searchResultFactory = $searchResultFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->creditmemoCommentSender = $creditmemoCommentSender
            ?: ObjectManager::getInstance()->get(CreditmemoCommentSender::class);
        $this->creditmemoRepository = $creditmemoRepository
            ?: ObjectManager::getInstance()->get(CreditmemoRepositoryInterface::class);
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function get($id)
    {
        $entity = $this->commentFactory->create();
        $this->commentResource->load($entity, $id);
        return $entity;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResult = $this->searchResultFactory->create();
        $this->collectionProcessor->process($searchCriteria, $searchResult);
        $searchResult->setSearchCriteria($searchCriteria);
        return $searchResult;
    }

    /**
     * @inheritdoc
     */
    public function delete(CreditmemoCommentInterface $entity)
    {
        try {
            $this->commentResource->delete($entity);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete the comment.'), $e);
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function save(CreditmemoCommentInterface $entity)
    {
        try {
            $this->commentResource->save($entity);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save the creditmemo comment.'), $e);
        }

        try {
            $creditmemo = $this->creditmemoRepository->get($entity->getParentId());
            $this->creditmemoCommentSender->send($creditmemo, $entity->getIsCustomerNotified(), $entity->getComment());
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
        }

        return $entity;
    }
}
