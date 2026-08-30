<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Catalog\Model;

use Magento\Catalog\Api\CategoryLinkRepositoryInterface;
use Magento\Catalog\Api\CategoryListDeleteBySkuInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Catalog\Model\ResourceModel\Product\CategoryLink;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;

/**
 * @inheritdoc
 */
class CategoryLinkRepository implements CategoryLinkRepositoryInterface, CategoryListDeleteBySkuInterface
{
    /**
     * Category data key holding the product id to position map
     */
    private const PRODUCTS_POSITION_KEY = 'products_position';

    /**
     * Category data key holding the product positions submitted for saving
     */
    private const POSTED_PRODUCTS_KEY = 'posted_products';

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Product
     */
    private $productResource;

    /**
     * @var CategoryLink
     */
    private $categoryLinkResource;

    /**
     * @param CategoryRepositoryInterface $categoryRepository
     * @param ProductRepositoryInterface $productRepository
     * @param Product $productResource
     * @param CategoryLink|null $categoryLinkResource
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        ProductRepositoryInterface $productRepository,
        ?Product $productResource = null,
        ?CategoryLink $categoryLinkResource = null
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
        $this->productResource = $productResource ?? ObjectManager::getInstance()->get(Product::class);
        $this->categoryLinkResource = $categoryLinkResource ?? ObjectManager::getInstance()->get(CategoryLink::class);
    }

    /**
     * @inheritdoc
     */
    public function save(\Magento\Catalog\Api\Data\CategoryProductLinkInterface $productLink)
    {
        $category = $this->categoryRepository->get($productLink->getCategoryId());
        $product = $this->productRepository->get($productLink->getSku());
        $productPositions = $this->narrowDownProductsPosition($category, $product);
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
        } finally {
            $this->resetProductsPosition($category);
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
        $product = $this->productRepository->get($sku);
        $productPositions = $this->narrowDownProductsPosition($category, $product);

        try {
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
        } finally {
            $this->resetProductsPosition($category);
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

    /**
     * Narrow down the category product positions map to the single product being linked or unlinked.
     *
     * The category save compares the posted products with the products currently assigned to the category. Loading
     * every assignment of the category is prohibitively expensive for large categories, while a single link change
     * only ever needs the assignment of the product it touches. The narrowed down map is pre-seeded on the category
     * so that the comparison performed during the save stays limited to that product.
     *
     * @param CategoryInterface $category
     * @param ProductInterface $product
     * @return array
     */
    private function narrowDownProductsPosition(CategoryInterface $category, ProductInterface $product): array
    {
        $productsPosition = [];
        $categoryLinks = $this->categoryLinkResource->getCategoryLinks($product, [(int)$category->getId()]);
        foreach ($categoryLinks as $categoryLink) {
            $productsPosition[$product->getId()] = $categoryLink['position'];
        }
        $category->setData(self::PRODUCTS_POSITION_KEY, $productsPosition);

        return $productsPosition;
    }

    /**
     * Drop the narrowed down data from the category so that a later reuse of the instance is not affected by it.
     *
     * @param CategoryInterface $category
     * @return void
     */
    private function resetProductsPosition(CategoryInterface $category): void
    {
        $category->unsetData(self::PRODUCTS_POSITION_KEY);
        $category->unsetData(self::POSTED_PRODUCTS_KEY);
    }
}
