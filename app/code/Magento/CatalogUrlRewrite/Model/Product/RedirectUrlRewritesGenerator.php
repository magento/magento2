<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Product;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\Product;
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
use mysql_xdevapi\Exception;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RedirectUrlRewritesGenerator
{
    /**
     * @var Product
     * @deprecated 100.1.4
     */
    protected $product;

    /**
     * @var ObjectRegistry
     * @deprecated 100.1.4
     */
    protected $productCategories;

    /**
     * @var UrlFinderInterface
     * @deprecated 100.1.4
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
     * @var AnchorUrlRewriteGenerator
     */
    private $anchorUrlRewriteGenerator;
    /**
     * @var CategoriesUrlRewriteGenerator
     */
    private $categoriesUrlRewriteGenerator;
    /**
     * @var \Magento\CatalogUrlRewrite\Model\Product\CanonicalUrlRewriteGenerator
     */
    private $canonicalUrlRewriteGenerator;


    /**
     * @param UrlFinderInterface $urlFinder
     * @param ProductUrlPathGenerator $productUrlPathGenerator
     * @param UrlRewriteFactory $urlRewriteFactory
     * @param UrlRewriteFinder|null $urlRewriteFinder
     * @param \Magento\UrlRewrite\Model\MergeDataProviderFactory|null $mergeDataProviderFactory
     * @param CategoryRepository|null $categoryRepository
     * @param AnchorUrlRewriteGenerator $anchorUrlRewriteGenerator
     * @param CategoriesUrlRewriteGenerator $categoriesUrlRewriteGenerator
     * @param \Magento\CatalogUrlRewrite\Model\Product\CanonicalUrlRewriteGenerator $canonicalUrlRewriteGenerator
     */
    public function __construct(
        UrlFinderInterface $urlFinder,
        ProductUrlPathGenerator $productUrlPathGenerator,
        UrlRewriteFactory $urlRewriteFactory,
        UrlRewriteFinder $urlRewriteFinder = null,
        MergeDataProviderFactory $mergeDataProviderFactory = null,
        CategoryRepository $categoryRepository = null,
        \Magento\CatalogUrlRewrite\Model\Product\AnchorUrlRewriteGenerator $anchorUrlRewriteGenerator,
        \Magento\CatalogUrlRewrite\Model\Product\CategoriesUrlRewriteGenerator $categoriesUrlRewriteGenerator,
        \Magento\CatalogUrlRewrite\Model\Product\CanonicalUrlRewriteGenerator $canonicalUrlRewriteGenerator
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
        $this->anchorUrlRewriteGenerator = $anchorUrlRewriteGenerator;
        $this->categoriesUrlRewriteGenerator = $categoriesUrlRewriteGenerator;
        $this->canonicalUrlRewriteGenerator = $canonicalUrlRewriteGenerator;
    }

    public function getCategoryRewrites($storeId, Product $product, ObjectRegistry $productCategories)
    {
        $mergeDataProvider = clone $this->mergeDataProviderPrototype;

        $mergeDataProvider->merge(
            $this->categoriesUrlRewriteGenerator->generate($storeId, $product, $productCategories)
        );

        $mergeDataProvider->merge(
            $this->anchorUrlRewriteGenerator->generate($storeId, $product, $productCategories)
        );

        return $mergeDataProvider->getData();
    }

    /**
     * Generate product rewrites based on current rewrites without anchor categories
     * @param int $storeId
     * @param Product $product
     * @param ObjectRegistry $productCategories
     * @return UrlRewrite[]
     */
    public function generate($storeId, Product $product, ObjectRegistry $productCategories)
    {
        $mergeDataProvider = clone $this->mergeDataProviderPrototype;

        $origProduct = clone $product;
        $origProduct->setData($product->getOrigData());

        $virtualUrlRewrites = $this->getCategoryRewrites($storeId, $origProduct, $productCategories);

        foreach ($virtualUrlRewrites as $urlRewrite) {
            $category = $this->retrieveCategoryFromMetadata($urlRewrite, $productCategories);
            if (!$category) {
                throw new Exception('Category not found');
            }
            $mergeDataProvider->merge(
                $urlRewrite->getIsAutogenerated()
                ? $this->generateForAutogenerated($urlRewrite, $storeId, $product, $category)
                : $this->generateForCustom($urlRewrite, $storeId, $product, $category)
            );
        }

        $mergeDataProvider->merge(
            $this->generateForAutogenerated(
                $this->canonicalUrlRewriteGenerator->generate($storeId, $origProduct)[0],
                $storeId,
                $product
            )
        );

        return $mergeDataProvider->getData();
    }

    /**
     * @param UrlRewrite $url
     * @param int $storeId
     * @param Category|null $category
     * @param Product|null $product
     * @return UrlRewrite[]
     */
    private function generateForAutogenerated($url, $storeId, $product, $category = null)
    {
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

    /**
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
     * @param UrlRewrite $url
     * @param ObjectRegistry|null $productCategories
     * @return Category|null
     */
    protected function retrieveCategoryFromMetadata($url, ObjectRegistry $productCategories = null)
    {
        $metadata = $url->getMetadata();
        if (isset($metadata['category_id'])) {
            $category = $productCategories->get($metadata['category_id']) ?: $this->categoryRepository->get($metadata['category_id']);
            return $category;
        }
        return null;
    }
}
