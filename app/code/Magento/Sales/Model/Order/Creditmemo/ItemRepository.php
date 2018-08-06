<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Creditmemo;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Sales\Api\CreditmemoItemRepositoryInterface;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Api\Data\CreditmemoItemInterfaceFactory;
use Magento\Sales\Api\Data\CreditmemoItemSearchResultInterfaceFactory;
use Magento\Sales\Model\Spi\CreditmemoItemResourceInterface;

class ItemRepository implements CreditmemoItemRepositoryInterface
{
    /**
     * @var CreditmemoItemResourceInterface
     */
    private $itemResource;

    /**
     * @var CreditmemoItemInterfaceFactory
     */
    private $itemFactory;

    /**
     * @var CreditmemoItemSearchResultInterfaceFactory
     */
    private $searchResultFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param CreditmemoItemResourceInterface $itemResource
     * @param CreditmemoItemInterfaceFactory $itemFactory
     * @param CreditmemoItemSearchResultInterfaceFactory $searchResultFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        CreditmemoItemResourceInterface $itemResource,
        CreditmemoItemInterfaceFactory $itemFactory,
        CreditmemoItemSearchResultInterfaceFactory $searchResultFactory,
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
    public function get($id)
    {
        $entity = $this->itemFactory->create();
        $this->itemResource->load($entity, $id);
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
    public function delete(CreditmemoItemInterface $entity)
    {
        try {
            $this->itemResource->delete($entity);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete the credit memo item.'), $e);
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function save(CreditmemoItemInterface $entity)
    {
        try {
            $this->itemResource->save($entity);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save the credit memo item.'), $e);
        }
        return $entity;
    }
}
