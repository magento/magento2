<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogGraphQl\Model\Resolver\Categories\Query;

use Magento\Catalog\Api\CategoryManagementInterface;
use Magento\Catalog\Api\Data\CategoryTreeInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\FormatterInterface;

/**
 * Retrieve filtered categories data based off given search criteria in a format that GraphQL can interpret.
 */
class Filter
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FormatterInterface
     */
    private $formatter;

    /**
     * @var CategoryManagementInterface
     */
    private $categoryManagement;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @param CategoryManagementInterface $categoryManagement
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     * @param FormatterInterface $formatter
     */
    public function __construct(
        CategoryManagementInterface $categoryManagement,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        FormatterInterface $formatter
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->formatter = $formatter;
        $this->categoryManagement = $categoryManagement;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * Filter catalog product data based off given search criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return array
     */
    public function getResult(SearchCriteriaInterface $searchCriteria) : array
    {
        $categoriesTree = $this->categoryManagement->getTree(2);
        $categoriesTreeOutput = $this->dataObjectProcessor
            ->buildOutputDataArray($categoriesTree, CategoryTreeInterface::class);
        return $categoriesTreeOutput;
    }
}
