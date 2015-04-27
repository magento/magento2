<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model;

use Magento\Cms\Api\Data;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class PageRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PageRepository implements PageRepositoryInterface
{
    /**
     * @var Resource\Page
     */
    protected $resource;

    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var Resource\Page\CollectionFactory
     */
    protected $pageCollectionFactory;

    /**
     * @var Data\PageSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var Data\PageInterfaceFactory
     */
    protected $dataPageFactory;

    /**
     * @param Resource\Page $resource
     * @param PageFactory $pageFactory
     * @param Data\PageInterfaceFactory $dataPageFactory
     * @param Resource\Page\CollectionFactory $pageCollectionFactory
     * @param Data\PageSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        Resource\Page $resource,
        PageFactory $pageFactory,
        Data\PageInterfaceFactory $dataPageFactory,
        Resource\Page\CollectionFactory $pageCollectionFactory,
        Data\PageSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->resource = $resource;
        $this->pageFactory = $pageFactory;
        $this->pageCollectionFactory = $pageCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataPageFactory = $dataPageFactory;
    }

    /**
     * Save Page data
     *
     * @param Data\PageInterface $page
     * @return Page
     * @throws CouldNotSaveException
     */
    public function save(Data\PageInterface $page)
    {
        try {
            $this->resource->save($page);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $page;
    }

    /**
     * Load Page data by given Page Identity
     *
     * @param string $pageId
     * @return Page
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($pageId)
    {
        $page = $this->pageFactory->create();
        $this->resource->load($page, $pageId);
        if (!$page->getId()) {
            throw new NoSuchEntityException(__('CMS Page with id "%1" does not exist.', $pageId));
        }
        return $page;
    }

    /**
     * Load Page data collection by given search criteria
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param SearchCriteriaInterface $criteria
     * @return Resource\Page\Collection
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $collection = $this->pageCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            $fields = [];
            $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() === 'store_id') {
                    $collection->addStoreFilter($filter->getValue(), false);
                    continue;
                }
                $condition = $filter->getConditionType() ?: 'eq';
                $fields[] = ['attribute' => $filter->getField(), $condition => $filter->getValue()];
            }
            if ($fields) {
                $collection->addFieldToFilter($fields, $conditions);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SearchCriteriaInterface::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        $pages = [];
        /** @var Page $pageModel */
        foreach ($collection as $pageModel) {
            $pages[] = $this->dataObjectHelper->populateWithArray(
                $this->dataPageFactory->create(),
                $pageModel->getData(),
                'Magento\Cms\Api\Data\PageInterface'
            );
        }
        $searchResults->setItems($pages);
        return $searchResults;
    }

    /**
     * Delete Page
     *
     * @param Data\PageInterface $page
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\PageInterface $page)
    {
        try {
            $this->resource->delete($page);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete Page by given Page Identity
     *
     * @param string $pageId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($pageId)
    {
        return $this->delete($this->getById($pageId));
    }
}
