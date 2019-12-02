<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\CouldNotSaveException;

class CategoryLinkRepository implements \Magento\Catalog\Api\CategoryLinkRepositoryInterface,
    \Magento\Catalog\Api\CategoryListDeleteBySkuInterface
{
    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    private $productResource;

    /**
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResource
     */
    public function __construct(
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product $productResource = null
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
        $this->productResource = $productResource ?? \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Catalog\Model\ResourceModel\Product::class);
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Magento\Catalog\Api\Data\CategoryProductLinkInterface $productLink)
    {
        $category = $this->categoryRepository->get($productLink->getCategoryId());
        $product = $this->productRepository->get($productLink->getSku());
        $productPositions = $category->getProductsPosition();
        $productPositions[$product->getId()] = $productLink->getPosition();
        $category->setPostedProducts($productPositions);
        try {
            $category->save();
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __(
                    'Could not save product "%1" with position %2 to category %3',
                    $product->getId(),
                    $productLink->getPosition(),
                    $category->getId()
                ),
                $e
            );
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Magento\Catalog\Api\Data\CategoryProductLinkInterface $productLink)
    {
        return $this->deleteByIds($productLink->getCategoryId(), $productLink->getSku());
    }

    /**
     * {@inheritdoc}
     */
    public function deleteByIds($categoryId, $sku)
    {
        $category = $this->categoryRepository->get($categoryId);
        $product = $this->productRepository->get($sku);
        $productPositions = $category->getProductsPosition();

        $productID = $product->getId();
        if (!isset($productPositions[$productID])) {
            throw new InputException(__("The category doesn't contain the specified product."));
        }
        $backupPosition = $productPositions[$productID];
        unset($productPositions[$productID]);

        $category->setPostedProducts($productPositions);
        try {
            $category->save();
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __(
                    'Could not save product "%product" with position %position to category %category',
                    [
                        "product" => $product->getId(),
                        "position" => $backupPosition,
                        "category" => $category->getId()
                    ]
                ),
                $e
            );
        }
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function deleteBySkus($categoryId, array $productSkuList)
    {
        $category = $this->categoryRepository->get($categoryId);
        $products = $this->productResource->getProductsIdsBySkus($productSkuList);

        if (!$products) {
            throw new InputException(__("The category doesn't contain the specified products."));
        }

        $productPositions = $category->getProductsPosition();

        foreach ($products as $productSku => $productId) {
            if (isset($productPositions[$productId])) {
                unset($productPositions[$productId]);
            }
        }

        $category->setPostedProducts($productPositions);

        try {
            $category->save();
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __(
                    'Could not save products "%products" to category %category',
                    [
                        "products" => implode(',', $productSkuList),
                        "category" => $category->getId()
                    ]
                ),
                $e
            );
        }

        return true;
    }
}
