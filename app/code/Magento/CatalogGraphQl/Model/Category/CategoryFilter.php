<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Category;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategorySearchResultsInterface;
use Magento\Catalog\Api\Data\CategorySearchResultsInterfaceFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\CatalogGraphQl\Model\Resolver\Categories\DataProvider\Category\CollectionProcessorInterface;
use Magento\CatalogGraphQl\Model\Category\Filter\SearchCriteria;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Category filter allows filtering category results by attributes.
 */
class CategoryFilter
{
    /**
     * @var CollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;

    /**
     * @var CategorySearchResultsInterfaceFactory
     */
    private $categorySearchResultsFactory;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var SearchCriteria
     */
    private $searchCriteria;

    /**
     * @param CollectionFactory $categoryCollectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param CategorySearchResultsInterfaceFactory $categorySearchResultsFactory
     * @param CategoryRepositoryInterface $categoryRepository
     * @param SearchCriteria $searchCriteria
     */
    public function __construct(
        CollectionFactory $categoryCollectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        CategorySearchResultsInterfaceFactory $categorySearchResultsFactory,
        CategoryRepositoryInterface $categoryRepository,
        SearchCriteria $searchCriteria
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->categorySearchResultsFactory = $categorySearchResultsFactory;
        $this->categoryRepository = $categoryRepository;
        $this->searchCriteria = $searchCriteria;
    }

    /**
     * Search for categories
     *
     * @param array $criteria
     * @param StoreInterface $store
     * @param array $attributeNames
     * @param ContextInterface $context
     * @return int[]
     * @throws InputException
     */
    public function getResult(array $criteria, StoreInterface $store, array $attributeNames, ContextInterface $context)
    {
        $searchCriteria = $this->searchCriteria->buildCriteria($criteria, $store);
        $collection = $this->categoryCollectionFactory->create();
        $this->extensionAttributesJoinProcessor->process($collection);
        $this->collectionProcessor->process($collection, $searchCriteria, $attributeNames, $context);

        /** @var CategorySearchResultsInterface $searchResult */
        $categories = $this->categorySearchResultsFactory->create();
        $categories->setSearchCriteria($searchCriteria);
        $categories->setItems($collection->getItems());
        $categories->setTotalCount($collection->getSize());

        $categoryIds = [];
        foreach ($categories->getItems() as $category) {
            $categoryIds[] = (int)$category->getId();
        }

        $totalPages = 0;
        if ($categories->getTotalCount() > 0 && $searchCriteria->getPageSize() > 0) {
            $totalPages = ceil($categories->getTotalCount() / $searchCriteria->getPageSize());
        }
        if ($searchCriteria->getCurrentPage() > $totalPages && $categories->getTotalCount() > 0) {
            throw new GraphQlInputException(
                __(
                    'currentPage value %1 specified is greater than the %2 page(s) available.',
                    [$searchCriteria->getCurrentPage(), $totalPages]
                )
            );
        }

        return [
            'category_ids' => $categoryIds,
            'total_count' => $categories->getTotalCount(),
            'page_info' => [
                'total_pages' => $totalPages,
                'page_size' => $searchCriteria->getPageSize(),
                'current_page' => $searchCriteria->getCurrentPage(),
            ]
        ];
    }
}
