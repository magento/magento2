<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model;

use Magento\Catalog\Api\CategoryListInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategorySearchResultsInterface;
use Magento\Catalog\Api\Data\CategorySearchResultsInterfaceFactory;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Eav\Model\Api\SearchCriteria\CollectionProcessor;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class for getting category list.
 */
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
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param CollectionFactory $categoryCollectionFactory
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param CategorySearchResultsInterfaceFactory $categorySearchResultsFactory
     * @param CategoryRepositoryInterface $categoryRepository
     * @param CollectionProcessorInterface|null $collectionProcessor
     * @param StoreManagerInterface|null $storeManager
     */
    public function __construct(
        CollectionFactory $categoryCollectionFactory,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        CategorySearchResultsInterfaceFactory $categorySearchResultsFactory,
        CategoryRepositoryInterface $categoryRepository,
        CollectionProcessorInterface $collectionProcessor = null,
        StoreManagerInterface $storeManager = null
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->categorySearchResultsFactory = $categorySearchResultsFactory;
        $this->categoryRepository = $categoryRepository;
        $this->collectionProcessor = $collectionProcessor
            ?? ObjectManager::getInstance()->get(CollectionProcessor::class); // @phpstan-ignore-line
        $this->storeManager = $storeManager ?? ObjectManager::getInstance()->get(StoreManagerInterface::class);
    }

    /**
     * @inheritdoc
     *
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var Collection $collection */
        $collection = $this->categoryCollectionFactory->create();
        $this->extensionAttributesJoinProcessor->process($collection);
        $this->collectionProcessor->process($searchCriteria, $collection);
        $currentStoreId = $this->storeManager->getStore()->getId();

        $items = [];
        foreach ($collection->getData() as $categoryData) {
            $items[] = $this->categoryRepository->get(
                $categoryData[$collection->getEntity()->getIdFieldName()],
                $currentStoreId
            );
        }

        /** @var CategorySearchResultsInterface $searchResult */
        $searchResult = $this->categorySearchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($items);
        $searchResult->setTotalCount($collection->getSize());
        return $searchResult;
    }
}
