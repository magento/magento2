<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\CatalogUrlRewrite\Model\Product;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Model\OptionProvider;
use Magento\CatalogUrlRewrite\Model\ObjectRegistry;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;
use Magento\CatalogUrlRewrite\Model\Map\UrlRewriteFinder;
use Magento\Framework\App\ObjectManager;
use Magento\UrlRewrite\Model\MergeDataProviderFactory;
use Magento\CatalogUrlRewrite\Block\UrlKeyRenderer;
use Magento\Store\Model\ScopeInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CurrentUrlRewritesRegenerator
{
    /**
     * @var Product
     * @deprecated 100.1.0
     * @see not used
     */
    protected $product;

    /**
     * @var ObjectRegistry
     * @deprecated 100.1.0
     * @see not used
     */
    protected $productCategories;

    /**
     * @var UrlFinderInterface
     * @deprecated 100.1.0
     * @see not used
     */
    protected $urlFinder;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator
     */
    protected $productUrlPathGenerator;

    /**
     * @var \Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory
     */
    protected $urlRewriteFactory;

    /**
     * @var \Magento\UrlRewrite\Service\V1\Data\UrlRewrite
     */
    private $urlRewritePrototype;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\Map\UrlRewriteFinder
     */
    private $urlRewriteFinder;

    /**
     * @var \Magento\UrlRewrite\Model\MergeDataProvider
     */
    private $mergeDataProviderPrototype;

    /**
     * @var \Magento\Catalog\Model\CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param UrlFinderInterface $urlFinder
     * @param ProductUrlPathGenerator $productUrlPathGenerator
     * @param UrlRewriteFactory $urlRewriteFactory
     * @param UrlRewriteFinder|null $urlRewriteFinder
     * @param \Magento\UrlRewrite\Model\MergeDataProviderFactory|null $mergeDataProviderFactory
     * @param CategoryRepository|null $categoryRepository
     * @param ScopeConfigInterface|null $scopeConfig
     */
    public function __construct(
        UrlFinderInterface $urlFinder,
        ProductUrlPathGenerator $productUrlPathGenerator,
        UrlRewriteFactory $urlRewriteFactory,
        ?UrlRewriteFinder $urlRewriteFinder = null,
        ?MergeDataProviderFactory $mergeDataProviderFactory = null,
        ?CategoryRepository $categoryRepository = null,
        ?ScopeConfigInterface $scopeConfig = null
    ) {
        $this->urlFinder = $urlFinder;
        $this->productUrlPathGenerator = $productUrlPathGenerator;
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->urlRewritePrototype = $urlRewriteFactory->create();
        $this->urlRewriteFinder = $urlRewriteFinder ?: ObjectManager::getInstance()->get(UrlRewriteFinder::class);
        if (!isset($mergeDataProviderFactory)) {
            $mergeDataProviderFactory = ObjectManager::getInstance()->get(MergeDataProviderFactory::class);
        }
        $this->categoryRepository = $categoryRepository ?: ObjectManager::getInstance()->get(CategoryRepository::class);
        $this->mergeDataProviderPrototype = $mergeDataProviderFactory->create();
        $this->scopeConfig = $scopeConfig ?: ObjectManager::getInstance()->get(ScopeConfigInterface::class);
    }

    /**
     * Generate product rewrites based on current rewrites without anchor categories
     *
     * @param int $storeId
     * @param Product $product
     * @param ObjectRegistry $productCategories
     * @param int|null $rootCategoryId
     * @return UrlRewrite[]
     */
    public function generate($storeId, Product $product, ObjectRegistry $productCategories, $rootCategoryId = null)
    {
        $mergeDataProvider = clone $this->mergeDataProviderPrototype;
        $currentUrlRewrites = $this->urlRewriteFinder->findAllByData(
            $product->getEntityId(),
            $storeId,
            ProductUrlRewriteGenerator::ENTITY_TYPE,
            $rootCategoryId
        );

        $isSaveHistory = $this->scopeConfig->isSetFlag(
            UrlKeyRenderer::XML_PATH_SEO_SAVE_HISTORY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $product->setData('save_rewrites_history', $isSaveHistory);

        foreach ($currentUrlRewrites as $currentUrlRewrite) {
            $category = $this->retrieveCategoryFromMetadata($currentUrlRewrite, $productCategories);
            if ($category === false) {
                continue;
            }
            $mergeDataProvider->merge(
                $currentUrlRewrite->getIsAutogenerated()
                ? $this->generateForAutogenerated($currentUrlRewrite, $storeId, $category, $product)
                : $this->generateForCustom($currentUrlRewrite, $storeId, $category, $product)
            );
        }

        return $mergeDataProvider->getData();
    }

    /**
     * Generate product rewrites for anchor categories based on current rewrites
     *
     * @param int $storeId
     * @param Product $product
     * @param ObjectRegistry $productCategories
     * @param int|null $rootCategoryId
     * @return UrlRewrite[]
     */
    public function generateAnchor(
        $storeId,
        Product $product,
        ObjectRegistry $productCategories,
        $rootCategoryId = null
    ) {
        $anchorCategoryIds = [];
        $mergeDataProvider = clone $this->mergeDataProviderPrototype;

        $currentUrlRewrites = $this->urlRewriteFinder->findAllByData(
            $product->getEntityId(),
            $storeId,
            ProductUrlRewriteGenerator::ENTITY_TYPE,
            $rootCategoryId
        );
        $anchorCategoryIds = array_merge(
            ...array_map(
                fn($productCategory) => $productCategory->getAnchorsAbove(),
                $productCategories->getList()
            )
        );

        foreach ($currentUrlRewrites as $currentUrlRewrite) {
            $metadata = $currentUrlRewrite->getMetadata();
            if (isset($metadata['category_id']) && $metadata['category_id'] > 0) {
                $category = $this->categoryRepository->get($metadata['category_id'], $storeId);
                if (in_array($category->getId(), $anchorCategoryIds)) {
                    $mergeDataProvider->merge(
                        $currentUrlRewrite->getIsAutogenerated()
                        ? $this->generateForAutogenerated($currentUrlRewrite, $storeId, $category, $product)
                        : $this->generateForCustom($currentUrlRewrite, $storeId, $category, $product)
                    );
                }
            }
        }

        return $mergeDataProvider->getData();
    }

    /**
     * Generate URL rewrites for autogenerated URLs
     *
     * @param UrlRewrite $url
     * @param int $storeId
     * @param Category|null $category
     * @param Product|null $product
     * @return UrlRewrite[]
     */
    protected function generateForAutogenerated($url, $storeId, $category, $product = null)
    {
        if ($product->getData('save_rewrites_history')) {
            $targetPath = $this->productUrlPathGenerator->getUrlPathWithSuffix($product, $storeId, $category);
            if ($url->getRequestPath() !== $targetPath) {
                $generatedUrl = clone $this->urlRewritePrototype;
                $generatedUrl->setEntityType(ProductUrlRewriteGenerator::ENTITY_TYPE)
                    ->setEntityId($product->getEntityId())
                    ->setRequestPath($url->getRequestPath())
                    ->setTargetPath($targetPath)
                    ->setRedirectType(OptionProvider::PERMANENT)
                    ->setStoreId($storeId)
                    ->setDescription($url->getDescription())
                    ->setIsAutogenerated(0)
                    ->setMetadata($url->getMetadata());
                return [$generatedUrl];
            }
        }
        return [];
    }

    /**
     * Generate URL rewrites for custom URLs
     *
     * @param UrlRewrite $url
     * @param int $storeId
     * @param Category|null $category
     * @param Product|null $product
     * @return UrlRewrite[]
     */
    protected function generateForCustom($url, $storeId, $category, $product = null)
    {
        $targetPath = $url->getRedirectType()
            ? $this->productUrlPathGenerator->getUrlPathWithSuffix($product, $storeId, $category)
            : $url->getTargetPath();
        if ($url->getRequestPath() !== $targetPath) {
            $generatedUrl = clone $this->urlRewritePrototype;
            $generatedUrl->setEntityType(ProductUrlRewriteGenerator::ENTITY_TYPE)
                ->setEntityId($product->getEntityId())
                ->setRequestPath($url->getRequestPath())
                ->setTargetPath($targetPath)
                ->setRedirectType($url->getRedirectType())
                ->setStoreId($storeId)
                ->setDescription($url->getDescription())
                ->setIsAutogenerated(0)
                ->setMetadata($url->getMetadata());
            return [$generatedUrl];
        }
        return [];
    }

    /**
     * Retrieve category from URL metadata
     *
     * @param UrlRewrite $url
     * @param ObjectRegistry|null $productCategories
     * @return Category|null|bool
     */
    protected function retrieveCategoryFromMetadata($url, ?ObjectRegistry $productCategories = null)
    {
        $metadata = $url->getMetadata();
        if (isset($metadata['category_id'])) {
            $category = $productCategories->get($metadata['category_id']);
            return $category === null ? false : $category;
        }
        return null;
    }
}
