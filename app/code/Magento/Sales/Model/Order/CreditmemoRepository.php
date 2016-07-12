<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order;

use Magento\Sales\Model\ResourceModel\Order\Creditmemo as Resource;
use Magento\Sales\Model\ResourceModel\Metadata;
use Magento\Sales\Api\Data\CreditmemoSearchResultInterfaceFactory as SearchResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Repository class for @see \Magento\Sales\Api\Data\CreditmemoInterface
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreditmemoRepository implements \Magento\Sales\Api\CreditmemoRepositoryInterface
{
    /**
     * @var Metadata
     */
    protected $metadata;

    /**
     * @var SearchResultFactory
     */
    protected $searchResultFactory = null;

    /**
     * \Magento\Sales\Api\Data\CreditmemoInterface[]
     *
     * @var array
     */
    protected $registry = [];

    /**
     * @param Metadata $metadata
     * @param SearchResultFactory $searchResultFactory
     */
    public function __construct(
        Metadata $metadata,
        SearchResultFactory $searchResultFactory
    ) {
        $this->metadata = $metadata;
        $this->searchResultFactory = $searchResultFactory;
    }

    /**
     * Loads a specified credit memo.
     *
     * @param int $id The credit memo ID.
     * @return \Magento\Sales\Api\Data\CreditmemoInterface Credit memo interface.
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function get($id)
    {
        if (!$id) {
            throw new InputException(__('Id required'));
        }
        if (!isset($this->registry[$id])) {
            /** @var \Magento\Sales\Api\Data\CreditmemoInterface $entity */
            $entity = $this->metadata->getNewInstance()->load($id);
            if (!$entity->getEntityId()) {
                throw new NoSuchEntityException(__('Requested entity doesn\'t exist'));
            }
            $this->registry[$id] = $entity;
        }
        return $this->registry[$id];
    }

    /**
     * Create credit memo instance
     *
     * @return \Magento\Sales\Api\Data\CreditmemoInterface
     */
    public function create()
    {
        return $this->metadata->getNewInstance();
    }

    /**
     * Lists credit memos that match specified search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteria $searchCriteria The search criteria.
     * @return \Magento\Sales\Api\Data\CreditmemoSearchResultInterface Credit memo search result interface.
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria)
    {
        /** @var \Magento\Sales\Api\Data\CreditmemoSearchResultInterface $searchResult */
        $searchResult = $this->searchResultFactory->create();
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                $searchResult->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setCurPage($searchCriteria->getCurrentPage());
        $searchResult->setPageSize($searchCriteria->getPageSize());
        return $searchResult;
    }

    /**
     * Deletes a specified credit memo.
     *
     * @param \Magento\Sales\Api\Data\CreditmemoInterface $entity The credit memo.
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(\Magento\Sales\Api\Data\CreditmemoInterface $entity)
    {
        try {
            $this->metadata->getMapper()->delete($entity);
            unset($this->registry[$entity->getEntityId()]);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete credit memo'), $e);
        }
        return true;
    }

    /**
     * Performs persist operations for a specified credit memo.
     *
     * @param \Magento\Sales\Api\Data\CreditmemoInterface $entity The credit memo.
     * @return \Magento\Sales\Api\Data\CreditmemoInterface Credit memo interface.
     * @throws CouldNotSaveException
     */
    public function save(\Magento\Sales\Api\Data\CreditmemoInterface $entity)
    {
        try {
            $this->metadata->getMapper()->save($entity);
            $this->registry[$entity->getEntityId()] = $entity;
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save credit memo'), $e);
        }
        return $this->registry[$entity->getEntityId()];
    }
}
