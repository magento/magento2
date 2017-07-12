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
    protected $_adminhtmlData = null;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $pageRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Backend\Helper\Data $adminhtmlData
     * @param \Magento\Cms\Api\PageRepositoryInterface $pageRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     */
    public function __construct(
        \Magento\Backend\Helper\Data $adminhtmlData,
        \Magento\Cms\Api\PageRepositoryInterface $pageRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder
    ) {
        $this->_adminhtmlData = $adminhtmlData;
        $this->pageRepository = $pageRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
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

        $this->searchCriteriaBuilder->setCurrentPage($searchCriteria->getStart());
        $this->searchCriteriaBuilder->setPageSize($searchCriteria->getLimit());
        $searchFields = ['title', 'identifier', 'content'];
        $filters = [];
        foreach ($searchFields as $field) {
            $filters[] = $this->filterBuilder
                ->setField($field)
                ->setConditionType('like')
                ->setValue($searchCriteria->getQuery() . '%')
                ->create();
        }
        $this->searchCriteriaBuilder->addFilters($filters);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchResults = $this->pageRepository->getList($searchCriteria);

        foreach ($searchResults->getItems() as $page) {
            $result[] = [
                'id' => 'cms-page/1/' . $page->getId(),
                'type' => __('CMS Page'),
                'name' => $page->getTitle(),
                'description' => $page->getIdentifier(),
                'url' => $this->_adminhtmlData->getUrl('cms/page/edit', ['page_id' => $page->getId()]),
            ];
        }
        return $result;
    }
}
