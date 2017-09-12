<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Shipment;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Sales\Api\Data\ShipmentCommentInterface;
use Magento\Sales\Api\Data\ShipmentCommentInterfaceFactory;
use Magento\Sales\Api\Data\ShipmentCommentSearchResultInterfaceFactory;
use Magento\Sales\Api\ShipmentCommentRepositoryInterface;
use Magento\Sales\Model\Spi\ShipmentCommentResourceInterface;

/**
 * @since 2.2.0
 */
class CommentRepository implements ShipmentCommentRepositoryInterface
{
    /**
     * @var ShipmentCommentResourceInterface
     */
    private $commentResource;

    /**
     * @var ShipmentCommentInterfaceFactory
     */
    private $commentFactory;

    /**
     * @var ShipmentCommentSearchResultInterfaceFactory
     */
    private $searchResultFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param ShipmentCommentResourceInterface $commentResource
     * @param ShipmentCommentInterfaceFactory $commentFactory
     * @param ShipmentCommentSearchResultInterfaceFactory $searchResultFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ShipmentCommentResourceInterface $commentResource,
        ShipmentCommentInterfaceFactory $commentFactory,
        ShipmentCommentSearchResultInterfaceFactory $searchResultFactory,
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
    public function delete(ShipmentCommentInterface $entity)
    {
        try {
            $this->commentResource->delete($entity);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete the shipment comment.'), $e);
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function save(ShipmentCommentInterface $entity)
    {
        try {
            $this->commentResource->save($entity);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save the shipment comment.'), $e);
        }
        return $entity;
    }
}
