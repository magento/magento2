<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model;

/**
 * Class CategoryLinkManagement
 */
class CategoryLinkManagement implements \Magento\Catalog\Api\CategoryLinkManagementInterface
{
    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ResourceModel\Product
     */
    protected $productResource;

    /**
     * @var \Magento\Catalog\Api\CategoryLinkRepositoryInterface
     */
    protected $categoryLinkRepository;

    /**
     * @var \Magento\Catalog\Api\Data\CategoryProductLinkInterfaceFactory
     */
    protected $productLinkFactory;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * CategoryLinkManagement constructor.
     *
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param ResourceModel\Product $productResource
     * @param \Magento\Catalog\Api\CategoryLinkRepositoryInterface $categoryLinkRepository
     * @param \Magento\Catalog\Api\Data\CategoryProductLinkInterfaceFactory $productLinkFactory
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     */
    public function __construct(
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        ResourceModel\Product $productResource,
        \Magento\Catalog\Api\CategoryLinkRepositoryInterface $categoryLinkRepository,
        \Magento\Catalog\Api\Data\CategoryProductLinkInterfaceFactory $productLinkFactory,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
        $this->productResource = $productResource;
        $this->categoryLinkRepository = $categoryLinkRepository;
        $this->productLinkFactory = $productLinkFactory;
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function getAssignedProducts($categoryId)
    {
        $category = $this->categoryRepository->get($categoryId);

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $products */
        $products = $category->getProductCollection();
        $products->addFieldToSelect('position');

        /** @var \Magento\Catalog\Api\Data\CategoryProductLinkInterface[] $links */
        $links = [];

        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($products->getItems() as $product) {
            /** @var \Magento\Catalog\Api\Data\CategoryProductLinkInterface $link */
            $link = $this->productLinkFactory->create();
            $link->setSku($product->getSku())
                ->setPosition($product->getData('cat_index_position'))
                ->setCategoryId($category->getId());
            $links[] = $link;
        }
        return $links;
    }

    /**
     * Assign product to given categories
     *
     * @param string $productSku
     * @param \int[] $categoryIds
     * @return bool
     */
    public function assignProductToCategories($productSku, array $categoryIds)
    {
        $product = $this->productRepository->get($productSku);
        $assignedCategories = $this->productResource->getCategoryIds($product);
        foreach (array_diff($assignedCategories, $categoryIds) as $categoryId) {
            $this->categoryLinkRepository->deleteByIds($categoryId, $productSku);
        }

        foreach (array_diff($categoryIds, $assignedCategories) as $categoryId) {
            /** @var \Magento\Catalog\Api\Data\CategoryProductLinkInterface $categoryProductLink */
            $categoryProductLink = $this->productLinkFactory->create();
            $categoryProductLink->setSku($productSku);
            $categoryProductLink->setCategoryId($categoryId);
            $categoryProductLink->setPosition(0);
            $this->categoryLinkRepository->save($categoryProductLink);
        }
        $productCategoryIndexer = $this->indexerRegistry->get(Indexer\Product\Category::INDEXER_ID);
        if (!$productCategoryIndexer->isScheduled()) {
            $productCategoryIndexer->reindexRow($product->getId());
        }
        return true;
    }
}
