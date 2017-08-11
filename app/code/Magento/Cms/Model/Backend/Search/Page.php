<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model\Backend\Search;

use Magento\Backend\Api\Search\ItemsInterface;
use Magento\Backend\Model\Search\SearchCriteria;

class Page implements ItemsInterface
{
    /**
     * Adminhtml data
     *
     * @var \Magento\Backend\Helper\Data
     */
    private $adminHtmlData = null;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $pageRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $criteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private $filterBuilder;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Backend\Helper\Data $adminHtmlData
     * @param \Magento\Cms\Api\PageRepositoryInterface $pageRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     */
    public function __construct(
        \Magento\Backend\Helper\Data $adminHtmlData,
        \Magento\Cms\Api\PageRepositoryInterface $pageRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder
    ) {
        $this->adminHtmlData = $adminHtmlData;
        $this->pageRepository = $pageRepository;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getResults(SearchCriteria $searchCriteria)
    {
        $result = [];
        if (!$searchCriteria->getStart() || !$searchCriteria->getLimit() || !$searchCriteria->getQuery()) {
            return $result;
        }

        $this->criteriaBuilder->setCurrentPage($searchCriteria->getStart());
        $this->criteriaBuilder->setPageSize($searchCriteria->getLimit());
        $searchFields = ['title', 'identifier', 'content'];
        $filters = [];
        foreach ($searchFields as $field) {
            $filters[] = $this->filterBuilder
                ->setField($field)
                ->setConditionType('like')
                ->setValue($searchCriteria->getQuery() . '%')
                ->create();
        }
        $this->criteriaBuilder->addFilters($filters);
        $searchCriteria = $this->criteriaBuilder->create();
        $searchResults = $this->pageRepository->getList($searchCriteria);

        foreach ($searchResults->getItems() as $page) {
            $result[] = [
                'id' => 'cms-page/1/' . $page->getId(),
                'type' => __('CMS Page'),
                'name' => $page->getTitle(),
                'description' => $page->getIdentifier(),
                'url' => $this->adminHtmlData->getUrl('cms/page/edit', ['page_id' => $page->getId()]),
            ];
        }
        return $result;
    }
}
