<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model;

use Magento\Catalog\Api\CategoryLinkRepositoryInterface;
use Magento\Catalog\Api\CategoryListDeleteBySkuInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;

/**
 * @inheritdoc
 */
class CategoryLinkRepository implements CategoryLinkRepositoryInterface, CategoryListDeleteBySkuInterface
{
    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @deprecated
     * @see Product use faster resource model
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Product
     */
    private $productResource;

    /**
     * @param CategoryRepositoryInterface $categoryRepository
     * @param ProductRepositoryInterface $productRepository
     * @param Product $productResource
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        ProductRepositoryInterface $productRepository,
        Product $productResource = null
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
        $this->productResource = $productResource ?? ObjectManager::getInstance()->get(Product::class);
    }

    /**
     * @inheritdoc
     */
    public function save(\Magento\Catalog\Api\Data\CategoryProductLinkInterface $productLink)
    {
        $category = $this->categoryRepository->get($productLink->getCategoryId());
        $productId = $this->productResource->getIdBySku($productLink->getSku());
        $productPositions = $category->getProductsPosition();
        $productPositions[$productId] = $productLink->getPosition();
        $category->setPostedProducts($productPositions);
        try {
            $category->save();
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __(
                    'Could not save product "%1" with position %2 to category %3',
                    $productId,
                    $productLink->getPosition(),
                    $category->getId()
                ),
                $e
            );
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function delete(\Magento\Catalog\Api\Data\CategoryProductLinkInterface $productLink)
    {
        return $this->deleteByIds($productLink->getCategoryId(), $productLink->getSku());
    }

    /**
     * @inheritdoc
     */
    public function deleteByIds($categoryId, $sku)
    {
        $category = $this->categoryRepository->get($categoryId);
        $productId = $this->productResource->getIdBySku($sku);
        $productPositions = $category->getProductsPosition();

        if (!isset($productPositions[$productId])) {
            throw new InputException(__("The category doesn't contain the specified product."));
        }
        $backupPosition = $productPositions[$productId];
        unset($productPositions[$productId]);

        $category->setPostedProducts($productPositions);
        try {
            $category->save();
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __(
                    'Could not save product "%product" with position %position to category %category',
                    [
                        "product" => $productId,
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
     * @inheritdoc
     */
    public function deleteBySkus(int $categoryId, array $productSkuList): bool
    {
        $category = $this->categoryRepository->get($categoryId);
        $products = $this->productResource->getProductsIdsBySkus($productSkuList);

        if (!$products) {
            throw new InputException(__("The category doesn't contain the specified products."));
        }

        $productPositions = $category->getProductsPosition();

        foreach ($products as $productId) {
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
