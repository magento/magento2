<?php

namespace Liip\CustomerHierarchy\Model\Role;

use Liip\CustomerHierarchy\Api\RoleManagerInterface;
use Liip\CustomerHierarchy\Model\Role;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\DB\Adapter\ConnectionException;
use Magento\Framework\DB\Adapter\DeadlockException;
use Magento\Framework\DB\Adapter\LockWaitException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Exception\CouldNotSaveException;

class Manager implements RoleManagerInterface
{
    /**
     * @var \Liip\CustomerHierarchy\Model\RoleFactory
     */
    private $roleFactory;

    /**
     * @var \Liip\CustomerHierarchy\Model\ResourceModel\Role\CollectionFactory
     */
    private $roleCollectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var \Magento\Framework\Api\SearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * Manager constructor.
     * @param \Liip\CustomerHierarchy\Model\RoleFactory $roleFactory
     * @param \Liip\CustomerHierarchy\Model\ResourceModel\Role\CollectionFactory $roleCollectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param \Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        \Liip\CustomerHierarchy\Model\RoleFactory $roleFactory,
        \Liip\CustomerHierarchy\Model\ResourceModel\Role\CollectionFactory $roleCollectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        \Magento\Framework\Api\SearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->roleFactory = $roleFactory;
        $this->roleCollectionFactory = $roleCollectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Liip\CustomerHierarchy\Model\ResourceModel\Role\Collection $collection */
        $collection = $this->roleCollectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        $collection->load();

        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());
        return $searchResult;
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        return 123;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        $role = $this->roleFactory->create();
        $role->load((int)$id);

        $roleId = $role->getId();
        if (!$roleId) {
            throw new NoSuchEntityException(__('Requested product doesn\'t exist'));
        }

        return $role;
    }
}
