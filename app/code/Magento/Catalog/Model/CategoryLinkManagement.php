<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model;

class CategoryLinkManagement implements \Magento\Catalog\Api\CategoryLinkManagementInterface
{
    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Catalog\Api\Data\CategoryProductLinkInterfaceFactory
     */
    protected $productLinkFactory;

    /**
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Catalog\Api\Data\CategoryProductLinkInterfaceFactory $productLinkFactory
     */
    public function __construct(
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Catalog\Api\Data\CategoryProductLinkInterfaceFactory $productLinkFactory
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->productLinkFactory = $productLinkFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getAssignedProducts($categoryId)
    {
        $category = $this->categoryRepository->get($categoryId);
        $productsPosition = $category->getProductsPosition();

        /** @var \Magento\Framework\Data\Collection\AbstractDb $products */
        $products = $category->getProductCollection();

        /** @var \Magento\Catalog\Api\Data\CategoryProductLinkInterface[] $links */
        $links = [];

        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($products->getItems() as $productId => $product) {
            /** @var \Magento\Catalog\Api\Data\CategoryProductLinkInterface $link */
            $link = $this->productLinkFactory->create();
            $link->setSku($product->getSku())
                ->setPosition($productsPosition[$productId])
                ->setCategoryId($category->getId());
            $links[] = $link;
        }
        return $links;
    }
}
