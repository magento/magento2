<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Invoice;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Sales\Api\Data\InvoiceCommentInterface;
use Magento\Sales\Api\Data\InvoiceCommentInterfaceFactory;
use Magento\Sales\Api\Data\InvoiceCommentSearchResultInterfaceFactory;
use Magento\Sales\Api\InvoiceCommentRepositoryInterface;
use Magento\Sales\Model\Spi\InvoiceCommentResourceInterface;

/**
 * @since 2.2.0
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
     * @param InvoiceCommentResourceInterface $commentResource
     * @param InvoiceCommentInterfaceFactory $commentFactory
     * @param InvoiceCommentSearchResultInterfaceFactory $searchResultFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        InvoiceCommentResourceInterface $commentResource,
        InvoiceCommentInterfaceFactory $commentFactory,
        InvoiceCommentSearchResultInterfaceFactory $searchResultFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->commentResource = $commentResource;
        $this->commentFactory = $commentFactory;
        $this->searchResultFactory = $searchResultFactory;
        $this->collectionProcessor = $collectionProcessor;
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
        return $entity;
    }
}
