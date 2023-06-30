<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Model\Search;

use Magento\Backend\Helper\Data;
use Magento\Catalog\Api\CategoryListInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Stdlib\StringUtils;

/**
 * Search model for backend search
 */
class Category extends DataObject
{
    /**
     * @var Data
     */
    private $adminhtmlData = null;

    /**
     * @var CategoryListInterface
     */
    private $categoryRepository;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $searchCriteriaBuilderFactory;

    /**
     * @var StringUtils
     */
    private $string;

    /**
     * @var SearchCriteriaBuilder|void
     */
    private $searchCriteriaBuilder;

    /**
     * @param Data $adminhtmlData
     * @param CategoryListInterface $categoryRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     * @param FilterBuilder $filterBuilder
     * @param StringUtils $string
     */
    public function __construct(
        Data $adminhtmlData,
        CategoryListInterface $categoryRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        FilterBuilder $filterBuilder,
        StringUtils $string
    ) {
        $this->adminhtmlData = $adminhtmlData;
        $this->categoryRepository = $categoryRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->filterBuilder = $filterBuilder;
        $this->string = $string;
    }

    /**
     * Load search results
     *
     * @return $this
     */
    public function load()
    {
        $result = [];
        if (!$this->hasStart() || !$this->hasLimit() || !$this->hasQuery()) {
            $this->setResults($result);
            return $this;
        }
        $this->searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $this->searchCriteriaBuilder->setCurrentPage($this->getStart());
        $this->searchCriteriaBuilder->setPageSize($this->getLimit());
        $searchFields = ['name'];

        $filters = [];
        foreach ($searchFields as $field) {
            $filters[] = $this->filterBuilder
                ->setField($field)
                ->setConditionType('like')
                ->setValue(sprintf("%%%s%%", $this->getQuery()))
                ->create();
        }
        $this->searchCriteriaBuilder->addFilters($filters);

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchResults = $this->categoryRepository->getList($searchCriteria);

        foreach ($searchResults->getItems() as $category) {
            $description = $category->getDescription() ? strip_tags($category->getDescription()) : '';
            $result[] = [
                'id' => sprintf('category/1/%d', $category->getId()),
                'type' => __('Category'),
                'name' => $category->getName(),
                'description' => $this->string->substr($description, 0, 30),
                'url' => $this->adminhtmlData->getUrl('catalog/category/edit', ['id' => $category->getId()]),
            ];
        }
        $this->setResults($result);
        return $this;
    }
}
