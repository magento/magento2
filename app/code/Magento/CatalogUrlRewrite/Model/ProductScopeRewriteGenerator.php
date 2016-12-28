<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\Product\CanonicalUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\Product\CategoriesUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\Product\CurrentUrlRewritesRegenerator;
use Magento\CatalogUrlRewrite\Model\Product\AnchorUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Service\V1\StoreViewService;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\UrlRewritesSetFactory;
use Magento\Framework\App\ObjectManager;

/**
 * Class ProductScopeRewriteGenerator
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductScopeRewriteGenerator
{
    /**
     * @var StoreViewService
     */
    private $storeViewService;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ObjectRegistryFactory
     */
    private $objectRegistryFactory;

    /**
     * @var AnchorUrlRewriteGenerator
     */
    private $anchorUrlRewriteGenerator;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\Product\CurrentUrlRewritesRegenerator
     */
    private $currentUrlRewritesRegenerator;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\Product\CategoriesUrlRewriteGenerator
     */
    private $categoriesUrlRewriteGenerator;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\Product\CanonicalUrlRewriteGenerator
     */
    private $canonicalUrlRewriteGenerator;

    /** @var \Magento\UrlRewrite\Model\UrlRewritesSet */
    private $urlRewritesSetPlaceHolder;

    /**
     * @param StoreViewService $storeViewService
     * @param StoreManagerInterface $storeManager
     * @param ObjectRegistryFactory $objectRegistryFactory
     * @param CanonicalUrlRewriteGenerator $canonicalUrlRewriteGenerator
     * @param CategoriesUrlRewriteGenerator $categoriesUrlRewriteGenerator
     * @param CurrentUrlRewritesRegenerator $currentUrlRewritesRegenerator
     * @param AnchorUrlRewriteGenerator $anchorUrlRewriteGenerator
     * @param \Magento\UrlRewrite\Model\UrlRewritesSetFactory|null $urlRewritesSetFactory
     */
    public function __construct(
        StoreViewService $storeViewService,
        StoreManagerInterface $storeManager,
        ObjectRegistryFactory $objectRegistryFactory,
        CanonicalUrlRewriteGenerator $canonicalUrlRewriteGenerator,
        CategoriesUrlRewriteGenerator $categoriesUrlRewriteGenerator,
        CurrentUrlRewritesRegenerator $currentUrlRewritesRegenerator,
        AnchorUrlRewriteGenerator $anchorUrlRewriteGenerator,
        UrlRewritesSetFactory $urlRewritesSetFactory = null
    ) {
        $this->storeViewService = $storeViewService;
        $this->storeManager = $storeManager;
        $this->objectRegistryFactory = $objectRegistryFactory;
        $this->canonicalUrlRewriteGenerator = $canonicalUrlRewriteGenerator;
        $this->categoriesUrlRewriteGenerator = $categoriesUrlRewriteGenerator;
        $this->currentUrlRewritesRegenerator = $currentUrlRewritesRegenerator;
        $this->anchorUrlRewriteGenerator = $anchorUrlRewriteGenerator;
        $urlRewritesSetFactory = $urlRewritesSetFactory ?: ObjectManager::getInstance()
            ->get(UrlRewritesSetFactory::class);
        $this->urlRewritesSetPlaceHolder = $urlRewritesSetFactory->create();
    }

    /**
     * Check is global scope
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isGlobalScope($storeId)
    {
        return null === $storeId || $storeId == Store::DEFAULT_STORE_ID;
    }

    /**
     * Generate url rewrites for global scope
     *
     * @param \Magento\Framework\Data\Collection|\Magento\Catalog\Model\Category[] $productCategories
     * @param Product $product
     * @param int|null $rootCategoryId
     * @return array
     */
    public function generateForGlobalScope($productCategories, Product $product, $rootCategoryId = null)
    {
        $productId = $product->getEntityId();
        $urlRewritesSet = clone $this->urlRewritesSetPlaceHolder;

        foreach ($product->getStoreIds() as $id) {
            if (!$this->isGlobalScope($id) &&
                !$this->storeViewService->doesEntityHaveOverriddenUrlKeyForStore(
                    $id,
                    $productId,
                    Product::ENTITY
                )) {
                $urlRewritesSet->merge(
                    $this->generateForSpecificStoreView($id, $productCategories, $product, $rootCategoryId)
                );
            }
        }

        $result = $urlRewritesSet->getData();
        $urlRewritesSet->resetData();
        return $result;
    }

    /**
     * Generate list of urls for specific store view
     *
     * @param int $storeId
     * @param \Magento\Framework\Data\Collection|Category[] $productCategories
     * @param \Magento\Catalog\Model\Product $product
     * @param int|null $rootCategoryId
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     */
    public function generateForSpecificStoreView($storeId, $productCategories, Product $product, $rootCategoryId = null)
    {
        $urlRewritesSet = clone $this->urlRewritesSetPlaceHolder;
        $categories = [];
        foreach ($productCategories as $category) {
            if ($this->isCategoryProperForGenerating($category, $storeId)) {
                $categories[] = $category;
            }
        }
        $productCategories = $this->objectRegistryFactory->create(['entities' => $categories]);

        $urlRewritesSet->merge(
            $this->canonicalUrlRewriteGenerator->generate($storeId, $product)
        );
        $urlRewritesSet->merge(
            $this->categoriesUrlRewriteGenerator->generate($storeId, $product, $productCategories)
        );
        $urlRewritesSet->merge(
            $this->currentUrlRewritesRegenerator->generate(
                $storeId,
                $product,
                $productCategories,
                $rootCategoryId
            )
        );
        $urlRewritesSet->merge(
            $this->anchorUrlRewriteGenerator->generate($storeId, $product, $productCategories)
        );

        $result = $urlRewritesSet->getData();
        $urlRewritesSet->resetData();
        return $result;
    }

    /**
     * Check possibility for url rewrite generation
     *
     * @param \Magento\Catalog\Model\Category $category
     * @param int $storeId
     * @return bool
     */
    public function isCategoryProperForGenerating(Category $category, $storeId)
    {
        if ($category->getParentId() != \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
            list(, $rootCategoryId) = $category->getParentIds();
            return $rootCategoryId == $this->storeManager->getStore($storeId)->getRootCategoryId();
        }
        return false;
    }
}
