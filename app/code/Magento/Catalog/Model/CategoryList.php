<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model;

use Magento\Catalog\Api\CategoryListInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategorySearchResultsInterface;
use Magento\Catalog\Api\Data\CategorySearchResultsInterfaceFactory;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;

class CategoryList implements CategoryListInterface
{
    /**
     * @var CollectionFactory
     */
    private $categoryCollectionFactory;

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
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param CollectionFactory $categoryCollectionFactory
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param CategorySearchResultsInterfaceFactory $categorySearchResultsFactory
     * @param CategoryRepositoryInterface $categoryRepository
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        CollectionFactory $categoryCollectionFactory,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        CategorySearchResultsInterfaceFactory $categorySearchResultsFactory,
        CategoryRepositoryInterface $categoryRepository,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->categorySearchResultsFactory = $categorySearchResultsFactory;
        $this->categoryRepository = $categoryRepository;
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var Collection $collection */
        $collection = $this->categoryCollectionFactory->create();
        $this->extensionAttributesJoinProcessor->process($collection);

        $this->collectionProcessor->process($searchCriteria, $collection);

        $items = [];
        foreach ($collection->getAllIds() as $id) {
            $items[] = $this->categoryRepository->get($id);
        }

        /** @var CategorySearchResultsInterface $searchResult */
        $searchResult = $this->categorySearchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($items);
        $searchResult->setTotalCount($collection->getSize());
        return $searchResult;
    }

    /**
     * Retrieve collection processor
     *
     * @deprecated
     * @return CollectionProcessorInterface
     */
    private function getCollectionProcessor()
    {
        if (!$this->collectionProcessor) {
            $this->collectionProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get(
                'Magento\Eav\Model\Api\SearchCriteria\CollectionProcessor'
            );
        }
        return $this->collectionProcessor;
    }
}
