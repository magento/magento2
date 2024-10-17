<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Category;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\CatalogGraphQl\Model\Resolver\Categories\DataProvider\Category\CollectionProcessorInterface;
use Magento\CatalogGraphQl\Model\Category\Filter\SearchCriteria;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\DB\Select;
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
     * @var SearchCriteria
     */
    private $searchCriteria;

    /**
     * @param CollectionFactory $categoryCollectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param SearchCriteria $searchCriteria
     */
    public function __construct(
        CollectionFactory $categoryCollectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        SearchCriteria $searchCriteria
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->searchCriteria = $searchCriteria;
    }

    /**
     * Search for categories
     *
     * @param array $criteria
     * @param StoreInterface $store
     * @param array $attributeNames
     * @param ContextInterface $context
     * @return array
     * @throws InputException
     */
    public function getResult(array $criteria, StoreInterface $store, array $attributeNames, ContextInterface $context)
    {
        $searchCriteria = $this->searchCriteria->buildCriteria($criteria, $store);
        $collection = $this->categoryCollectionFactory->create();
        $this->extensionAttributesJoinProcessor->process($collection);
        $this->collectionProcessor->process($collection, $searchCriteria, $attributeNames, $context);

        // only fetch necessary category entity id
        $collection
            ->getSelect()
            ->reset(Select::COLUMNS)
            ->columns(
                'e.entity_id'
            );
        $collection->setOrder('entity_id');

        $categoryIds = $collection->load()->getLoadedIds();

        $totalPages = 0;
        if ($collection->getSize() > 0 && $searchCriteria->getPageSize() > 0) {
            $totalPages = ceil($collection->getSize() / $searchCriteria->getPageSize());
        }
        if ($searchCriteria->getCurrentPage() > $totalPages && $collection->getSize() > 0) {
            throw new GraphQlInputException(
                __(
                    'currentPage value %1 specified is greater than the %2 page(s) available.',
                    [$searchCriteria->getCurrentPage(), $totalPages]
                )
            );
        }

        return [
            'category_ids' => $categoryIds,
            'total_count' => $collection->getSize(),
            'page_info' => [
                'total_pages' => $totalPages,
                'page_size' => $searchCriteria->getPageSize(),
                'current_page' => $searchCriteria->getCurrentPage(),
            ]
        ];
    }
}
