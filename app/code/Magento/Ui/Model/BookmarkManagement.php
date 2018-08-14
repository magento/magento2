<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Model;

class BookmarkManagement implements \Magento\Ui\Api\BookmarkManagementInterface
{
    /**
     * @var \Magento\Ui\Api\BookmarkRepositoryInterface
     */
    protected $bookmarkRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    protected $userContext;

    /**
     * @param \Magento\Ui\Api\BookmarkRepositoryInterface $bookmarkRepository
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     */
    public function __construct(
        \Magento\Ui\Api\BookmarkRepositoryInterface $bookmarkRepository,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Authorization\Model\UserContextInterface $userContext
    ) {
        $this->bookmarkRepository = $bookmarkRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->userContext = $userContext;
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
