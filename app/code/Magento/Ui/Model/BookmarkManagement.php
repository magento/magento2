<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Ui\Api\BookmarkManagementInterface;
use Magento\Ui\Api\BookmarkRepositoryInterface;

/**
 * Bookmark Management class provide functional for retrieving bookmarks by params
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class BookmarkManagement implements BookmarkManagementInterface
{
    /**
     * @var BookmarkRepositoryInterface
     */
    protected $bookmarkRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var UserContextInterface
     */
    protected $userContext;

    /**
     * @var array
     */
    private $bookmarkRegistry = [];

    /**
     * @param BookmarkRepositoryInterface $bookmarkRepository
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param UserContextInterface $userContext
     */
    public function __construct(
        BookmarkRepositoryInterface $bookmarkRepository,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        UserContextInterface $userContext
    ) {
        $this->bookmarkRepository = $bookmarkRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->userContext = $userContext;
    }

    /**
     * Create search criteria builder with namespace and user filters
     *
     * @param $namespace
     * @return void
     */
    private function prepareSearchCriteriaBuilderByNamespace($namespace): void
    {
        $userIdFilter = $this->filterBuilder
            ->setField('user_id')
            ->setConditionType('eq')
            ->setValue($this->userContext->getUserId())
            ->create();
        $namespaceFilter = $this->filterBuilder
            ->setField('namespace')
            ->setConditionType('eq')
            ->setValue($namespace)
            ->create();

        $this->searchCriteriaBuilder->addFilters([$userIdFilter]);
        $this->searchCriteriaBuilder->addFilters([$namespaceFilter]);
    }

    /**
     * {@inheritdoc}
     */
    public function loadByNamespace($namespace)
    {
        $this->prepareSearchCriteriaBuilderByNamespace($namespace);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        return $this->bookmarkRepository->getList($searchCriteria);
    }

    /**
     * {@inheritdoc}
     */
    public function getByIdentifierNamespace($identifier, $namespace)
    {
        if (!isset($this->bookmarkRegistry[$identifier . $namespace])) {
            $this->prepareSearchCriteriaBuilderByNamespace($namespace);
            $identifierFilter = $this->filterBuilder
                ->setField('identifier')
                ->setConditionType('eq')
                ->setValue($identifier)
                ->create();
            $this->searchCriteriaBuilder->addFilters([$identifierFilter]);

            $searchCriteria = $this->searchCriteriaBuilder->create();
            $searchResults = $this->bookmarkRepository->getList($searchCriteria);
            if ($searchResults->getTotalCount() > 0) {
                $items = $searchResults->getItems();
                $this->bookmarkRegistry[$identifier . $namespace] = array_shift($items);
                return $this->bookmarkRegistry[$identifier . $namespace];
            }
        }

        return $this->bookmarkRegistry[$identifier . $namespace] ?? null;
    }
}
