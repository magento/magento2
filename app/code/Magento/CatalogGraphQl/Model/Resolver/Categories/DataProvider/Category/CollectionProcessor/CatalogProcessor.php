<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Categories\DataProvider\Category\CollectionProcessor;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\CatalogGraphQl\Model\Resolver\Categories\DataProvider\Category\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\DB\Sql\Expression;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface as SearchCriteriaCollectionProcessor;

/**
 * Apply pre-defined catalog filtering
 *
 * {@inheritdoc}
 */
class CatalogProcessor implements CollectionProcessorInterface
{
    /**
     * @var SearchCriteriaCollectionProcessor
     */
    private $collectionProcessor;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @param SearchCriteriaCollectionProcessor $collectionProcessor
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        SearchCriteriaCollectionProcessor $collectionProcessor,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->collectionProcessor = $collectionProcessor;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Process collection to add additional joins, attributes, and clauses to a category collection.
     *
     * @param Collection $collection
     * @param SearchCriteriaInterface $searchCriteria
     * @param array $attributeNames
     * @param ContextInterface|null $context
     * @return Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function process(
        Collection $collection,
        SearchCriteriaInterface $searchCriteria,
        array $attributeNames,
        ?ContextInterface $context = null
    ): Collection {
        $this->collectionProcessor->process($searchCriteria, $collection);
        $store = $context->getExtensionAttributes()->getStore();
        $category = $this->categoryRepository->get($store->getRootCategoryId());
        $this->addRootCategoryFilterForStoreByPath($collection, $category->getPath());
        return $collection;
    }

    /**
     * Add filtration based on the store root category id
     *
     * @param Collection $collection
     * @param string $storeRootCategoryPath
     */
    private function addRootCategoryFilterForStoreByPath(Collection $collection, string $storeRootCategoryPath) : void
    {
        $collection->addFieldToFilter(
            'path',
            [
                ['eq' => $storeRootCategoryPath],
                ['like' => new Expression(
                    $collection->getConnection()->quoteInto('?', $storeRootCategoryPath . '/%')
                )]
            ]
        );
    }
}
