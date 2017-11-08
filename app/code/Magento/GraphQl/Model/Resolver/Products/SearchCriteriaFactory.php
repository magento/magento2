<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Resolver\Products;

use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Product field resolver, used for GraphQL request processing.
 */
class SearchCriteriaFactory
{
    const DEFAULT_PAGE_SIZE = 20;

    /** @var \Magento\Framework\Api\SearchCriteriaInterfaceFactory */
    private $searchCriteriaFactory;

    /** @var SortOrderBuilder */
    private $sortOrderBuilder;

    /** @var FilterGroupFactory */
    private $filterGroupFactory;

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterfaceFactory $searchCriteriaFactory
     * @param \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder
     * @param FilterGroupFactory $filterGroupFactory
     */
    public function __construct(
        \Magento\Framework\Api\SearchCriteriaInterfaceFactory $searchCriteriaFactory,
        \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder,
        FilterGroupFactory $filterGroupFactory
    ) {
        $this->searchCriteriaFactory = $searchCriteriaFactory;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->filterGroupFactory = $filterGroupFactory;
    }

    /**
     * Creates a search criteria from an AST
     *
     * @param ResolveInfo $info
     * @return \Magento\Framework\Api\SearchCriteriaInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function create(ResolveInfo $info)
    {
        $pageSize = self::DEFAULT_PAGE_SIZE;
        $currentPage = 1;

        /** @var \GraphQL\Language\AST\FieldNode $fieldNode */
        $fieldNode = current($info->fieldNodes);

        $searchCriteria = $this->searchCriteriaFactory->create();

        foreach ($fieldNode->arguments as $argument) {
            switch ($argument->name->value) {
                case 'find':
                    $searchCriteria->setFilterGroups($this->filterGroupFactory->create($info));
                    break;
                case 'pageSize':
                    $pageSize = $argument->value->value;
                    break;
                case 'currentPage':
                    $currentPage = $argument->value->value;
                    break;
                case 'sort':
                    $sortOrders = [];
                    foreach ($argument->value->fields as $node) {
                        /** @var SortOrder $sortOrder */
                        $sortOrders[] = $this->sortOrderBuilder->setField($node->name->value)
                            ->setDirection($node->value->value == 'DESC' ? SortOrder::SORT_DESC : SortOrder::SORT_ASC)
                            ->create();
                    }
                    $searchCriteria->setSortOrders($sortOrders);
                    break;
            }
        }
        $searchCriteria->setPageSize($pageSize);
        $searchCriteria->setCurrentPage($currentPage);

        return $searchCriteria;
    }
}
