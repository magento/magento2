<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Catalog\Api\CategoryListInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppArea adminhtml
 */
class UrlRewriteHandlerTest extends TestCase
{
    /**
     * @var UrlRewriteHandler
     */
    private $handler;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->handler = $this->objectManager->get(UrlRewriteHandler::class);
    }

    /**
     * Checks category URLs rewrites generation with enabled `Use Categories Path for Product URLs` option and
     * store's specific product URL key.
     *
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/CatalogUrlRewrite/Fixtures/product_custom_url_key.php
     * @magentoConfigFixture admin_store catalog/seo/product_use_categories 1
     */
    public function testGenerateProductUrlRewrites()
    {
        $product = $this->getProduct('p002');
        $category = $this->getCategory('category 1');
        // change the category scope to the global
        $category->setStoreId(0)
            ->setChangedProductIds([$product->getId()])
            ->setAffectedProductIds([$product->getId()])
            ->setAnchorsAbove(false);

        $generatedUrls = $this->handler->generateProductUrlRewrites($category);
        $actual = array_values(array_map(function (UrlRewrite $urlRewrite) {
            return $urlRewrite->getRequestPath();
        }, $generatedUrls));

        $expected = [
            'store-1-key.html', // the Default store
            'cat-1/store-1-key.html', // the Default store with Category URL key
            '/store-1-key.html', // an anchor URL the Default store
            'p002.html', // the Secondary store
            'cat-1-2/p002.html', // the Secondary store with Category URL key
            '/p002.html', // an anchor URL the Secondary store
        ];
        self::assertEquals($expected, $actual, 'Generated URLs rewrites do not match.');
    }

    /**
     * Gets category by name.
     *
     * @param string $name
     * @return CategoryInterface
     */
    private function getCategory(string $name): CategoryInterface
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('name', $name)
            ->create();
        /** @var CategoryListInterface $repository */
        $repository = $this->objectManager->get(CategoryListInterface::class);
        $items = $repository->getList($searchCriteria)
            ->getItems();

        return array_pop($items);
    }

    /**
     * Gets product by SKU.
     *
     * @param string $sku
     * @return ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getProduct(string $sku): ProductInterface
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        return $productRepository->get($sku);
    }
}
