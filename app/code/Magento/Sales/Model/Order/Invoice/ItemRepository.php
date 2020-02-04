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
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\Data\InvoiceItemInterfaceFactory;
use Magento\Sales\Api\Data\InvoiceItemSearchResultInterfaceFactory;
use Magento\Sales\Api\InvoiceItemRepositoryInterface;
use Magento\Sales\Model\Spi\InvoiceItemResourceInterface;

class ItemRepository implements InvoiceItemRepositoryInterface
{
    /**
     * @var InvoiceItemResourceInterface
     */
    private $itemResource;

    /**
     * @var InvoiceItemInterfaceFactory
     */
    private $itemFactory;

    /**
     * @var InvoiceItemSearchResultInterfaceFactory
     */
    private $searchResultFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param InvoiceItemResourceInterface $itemResource
     * @param InvoiceItemInterfaceFactory $itemFactory
     * @param InvoiceItemSearchResultInterfaceFactory $searchResultFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        InvoiceItemResourceInterface $itemResource,
        InvoiceItemInterfaceFactory $itemFactory,
        InvoiceItemSearchResultInterfaceFactory $searchResultFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->itemResource = $itemResource;
        $this->itemFactory = $itemFactory;
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
        $entity = $this->itemFactory->create();
        $this->itemResource->load($entity, $id);
        return $entity;
    }

    /**
     * @inheritdoc
     */
    public function delete(InvoiceItemInterface $entity)
    {
        try {
            $this->itemResource->delete($entity);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete the invoice item.'), $e);
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function save(InvoiceItemInterface $entity)
    {
        try {
            $this->itemResource->save($entity);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save the invoice item.'), $e);
        }
        return $entity;
    }
}
