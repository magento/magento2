<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Ui\Api\BookmarkManagementInterface;
use Magento\Ui\Api\BookmarkRepositoryInterface;

class BookmarkManagement implements BookmarkManagementInterface
{
    /**
     * @param BookmarkRepositoryInterface $bookmarkRepository
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param UserContextInterface $userContext
     */
    public function __construct(
        protected readonly BookmarkRepositoryInterface $bookmarkRepository,
        protected readonly FilterBuilder $filterBuilder,
        protected readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        protected readonly UserContextInterface $userContext
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function loadByNamespace($namespace)
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

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchResults = $this->bookmarkRepository->getList($searchCriteria);

        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function getByIdentifierNamespace($identifier, $namespace)
    {
        $userIdFilter = $this->filterBuilder
            ->setField('user_id')
            ->setConditionType('eq')
            ->setValue($this->userContext->getUserId())
            ->create();
        $identifierFilter = $this->filterBuilder
            ->setField('identifier')
            ->setConditionType('eq')
            ->setValue($identifier)
            ->create();
        $namespaceFilter = $this->filterBuilder
            ->setField('namespace')
            ->setConditionType('eq')
            ->setValue($namespace)
            ->create();

        $this->searchCriteriaBuilder->addFilters([$userIdFilter]);
        $this->searchCriteriaBuilder->addFilters([$identifierFilter]);
        $this->searchCriteriaBuilder->addFilters([$namespaceFilter]);

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchResults = $this->bookmarkRepository->getList($searchCriteria);
        if ($searchResults->getTotalCount() > 0) {
            foreach ($searchResults->getItems() as $searchResult) {
                $bookmark = $this->bookmarkRepository->getById($searchResult->getId());
                return $bookmark;
            }
        }

        return null;
    }
}
