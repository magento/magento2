<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Model\ResourceModel;

use Exception;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Api\BookmarkRepositoryInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Ui\Api\Data\BookmarkInterface;
use Magento\Ui\Api\Data\BookmarkInterfaceFactory;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;
use Magento\Ui\Model\ResourceModel\Bookmark\Collection;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class BookmarkRepository
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BookmarkRepository implements BookmarkRepositoryInterface
{
    /**
     * @param BookmarkInterfaceFactory $bookmarkFactory
     * @param Bookmark $bookmarkResourceModel
     * @param BookmarkSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface | null $collectionProcessor
     */
    public function __construct(
        protected readonly BookmarkInterfaceFactory $bookmarkFactory,
        protected readonly Bookmark $bookmarkResourceModel,
        protected readonly BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        private ?CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
    }

    /**
     * Save bookmark.
     *
     * @param BookmarkInterface $bookmark
     * @return BookmarkInterface
     * @throws CouldNotSaveException
     */
    public function save(BookmarkInterface $bookmark)
    {
        try {
            $this->bookmarkResourceModel->save($bookmark);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $bookmark;
    }

    /**
     * Retrieve bookmark.
     *
     * @param int $bookmarkId
     * @return BookmarkInterface
     * @throws NoSuchEntityException
     */
    public function getById($bookmarkId)
    {
        $bookmark = $this->bookmarkFactory->create();
        $this->bookmarkResourceModel->load($bookmark, $bookmarkId);
        if (!$bookmark->getId()) {
            throw new NoSuchEntityException(
                __('The bookmark with "%1" ID doesn\'t exist. Verify your information and try again.', $bookmarkId)
            );
        }
        return $bookmark;
    }

    /**
     * Retrieve bookmarks matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var Collection $collection */
        $collection = $this->bookmarkFactory->create()->getCollection();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults->setTotalCount($collection->getSize());

        $bookmarks = [];
        /** @var BookmarkInterface $bookmark */
        foreach ($collection->getItems() as $bookmark) {
            $bookmarks[] = $this->getById($bookmark->getId());
        }
        $searchResults->setItems($bookmarks);

        return $searchResults;
    }

    /**
     * Delete bookmark.
     *
     * @param BookmarkInterface $bookmark
     * @return bool true on success
     * @throws CouldNotDeleteException
     */
    public function delete(BookmarkInterface $bookmark)
    {
        try {
            $this->bookmarkResourceModel->delete($bookmark);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete bookmark by ID.
     *
     * @param int $bookmarkId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteById($bookmarkId)
    {
        return $this->delete($this->getById($bookmarkId));
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection $collection
     * @return void
     * @deprecated 101.0.0
     * @throws InputException
     */
    protected function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $collection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Retrieve collection processor
     *
     * @deprecated 101.0.0
     * @return CollectionProcessorInterface
     */
    private function getCollectionProcessor()
    {
        if (!$this->collectionProcessor) {
            $this->collectionProcessor = ObjectManager::getInstance()->get(
                CollectionProcessorInterface::class
            );
        }
        return $this->collectionProcessor;
    }
}
