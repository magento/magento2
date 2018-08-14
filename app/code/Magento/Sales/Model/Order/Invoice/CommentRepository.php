<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Invoice;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Sales\Api\Data\InvoiceCommentInterface;
use Magento\Sales\Api\Data\InvoiceCommentInterfaceFactory;
use Magento\Sales\Api\Data\InvoiceCommentSearchResultInterfaceFactory;
use Magento\Sales\Api\InvoiceCommentRepositoryInterface;
use Magento\Sales\Model\Spi\InvoiceCommentResourceInterface;
use Magento\Sales\Model\Order\Email\Sender\InvoiceCommentSender;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Class CommentRepository
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CommentRepository implements InvoiceCommentRepositoryInterface
{
    /**
     * @var InvoiceCommentResourceInterface
     */
    private $commentResource;

    /**
     * @var InvoiceCommentInterfaceFactory
     */
    private $commentFactory;

    /**
     * @var InvoiceCommentSearchResultInterfaceFactory
     */
    private $searchResultFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var InvoiceCommentSender
     */
    private $invoiceCommentSender;

    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param InvoiceCommentResourceInterface $commentResource
     * @param InvoiceCommentInterfaceFactory $commentFactory
     * @param InvoiceCommentSearchResultInterfaceFactory $searchResultFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param InvoiceCommentSender|null $invoiceCommentSender
     * @param InvoiceRepositoryInterface|null $invoiceRepository
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        InvoiceCommentResourceInterface $commentResource,
        InvoiceCommentInterfaceFactory $commentFactory,
        InvoiceCommentSearchResultInterfaceFactory $searchResultFactory,
        CollectionProcessorInterface $collectionProcessor,
        InvoiceCommentSender $invoiceCommentSender = null,
        InvoiceRepositoryInterface $invoiceRepository = null,
        LoggerInterface $logger = null
    ) {
        $this->commentResource = $commentResource;
        $this->commentFactory = $commentFactory;
        $this->searchResultFactory = $searchResultFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->invoiceCommentSender = $invoiceCommentSender
            ?:ObjectManager::getInstance()->get(InvoiceCommentSender::class);
        $this->invoiceRepository = $invoiceRepository
            ?:ObjectManager::getInstance()->get(InvoiceRepositoryInterface::class);
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
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
    public function get($id)
    {
        $entity = $this->commentFactory->create();
        $this->commentResource->load($entity, $id);
        return $entity;
    }

    /**
     * @inheritdoc
     */
    public function delete(InvoiceCommentInterface $entity)
    {
        try {
            $this->commentResource->delete($entity);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete the invoice comment.'), $e);
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function save(InvoiceCommentInterface $entity)
    {
        try {
            $this->commentResource->save($entity);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save the invoice comment.'), $e);
        }

        try {
            $invoice = $this->invoiceRepository->get($entity->getParentId());
            $this->invoiceCommentSender->send($invoice, $entity->getIsCustomerNotified(), $entity->getComment());
        } catch (\Exception $exception) {
            $this->logger->warning('Something went wrong while sending email.');
        }
        return $entity;
    }
}
