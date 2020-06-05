<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Search;
/**
 * Search model for backend search
 *
 * @method Category setQuery(string $query)
 * @method string|null getQuery()
 * @method bool hasQuery()
 * @method Category setStart(int $startPosition)
 * @method int|null getStart()
 * @method bool hasStart()
 * @method Category setLimit(int $limit)
 * @method int|null getLimit()
 * @method bool hasLimit()
 * @method Category setResults(array $results)
 * @method array getResults()
 * @api
 */
class Category extends \Magento\Framework\DataObject
{
    /**
     * Adminhtml data
     *
     * @var \Magento\Backend\Helper\Data
     */
    protected $_adminhtmlData = null;

    /**
     * @var \Magento\Catalog\Api\CategoryListInterface
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * Magento string lib
     *
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $string;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Backend\Helper\Data $adminhtmlData
     * @param \Magento\Catalog\Api\CategoryListInterface $categoryRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\Stdlib\StringUtils $string
     */
    public function __construct(
        \Magento\Backend\Helper\Data $adminhtmlData,
        \Magento\Catalog\Api\CategoryListInterface $categoryRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Stdlib\StringUtils $string
    )
    {
        $this->_adminhtmlData = $adminhtmlData;
        $this->categoryRepository = $categoryRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
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

        $this->searchCriteriaBuilder->setCurrentPage($this->getStart());
        $this->searchCriteriaBuilder->setPageSize($this->getLimit());
        $searchFields = ['name'];

        $filters = [];
        foreach ($searchFields as $field) {
            $filters[] = $this->filterBuilder
                ->setField($field)
                ->setConditionType('like')
                ->setValue('%' . $this->getQuery() . '%')
                ->create();
        }
        $this->searchCriteriaBuilder->addFilters($filters);

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchResults = $this->categoryRepository->getList($searchCriteria);

        foreach ($searchResults->getItems() as $category) {
            $description = strip_tags($category->getDescription());
            $result[] = [
                'id' => 'category/1/' . $category->getId(),
                'type' => __('Category'),
                'name' => $category->getName(),
                'description' => $this->string->substr($description, 0, 30),
                'url' => $this->_adminhtmlData->getUrl('catalog/category/edit', ['id' => $category->getId()]),
            ];
        }
        $this->setResults($result);
        return $this;
    }
}
