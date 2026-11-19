<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite as UrlRewriteDTO;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Test of getting URL rewrites data from products
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UrlRewritesTest extends GraphQlAbstract
{
    /**
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_virtual.php
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testProductWithNoCategoriesAssigned()
    {
        $productSku = 'virtual-product';
        $query
            = <<<QUERY
{
    products (filter: {sku: {eq: "{$productSku}"}}) {
        items {
            name,
            sku,
            description {
                html
            }
            url_rewrites {
              url,
              parameters {
                name,
                value
              }
            }
        }
    }
}
QUERY;

        $response = $this->graphQlQuery($query);

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        $product = $productRepository->get('virtual-product', false, null, true);

        $storeId = ObjectManager::getInstance()->get(StoreManagerInterface::class)->getStore()->getId();
        $urlFinder = ObjectManager::getInstance()->get(UrlFinderInterface::class);
        $entityType = ObjectManager::getInstance()->create(EavConfig::class)->getEntityType('catalog_product');

        $entityTypeCode = $entityType->getEntityTypeCode();
        if ($entityTypeCode === 'catalog_product') {
            $entityTypeCode = 'product';
        }

        $rewritesCollection = $urlFinder->findAllByData(
            [
                UrlRewriteDTO::ENTITY_ID => $product->getId(),
                UrlRewriteDTO::ENTITY_TYPE => $entityTypeCode,
                UrlRewriteDTO::STORE_ID => $storeId
            ]
        );

        /* There should be only one rewrite */
        /** @var UrlRewriteDTO $urlRewrite */
        $urlRewrite = current($rewritesCollection);

        $this->assertArrayHasKey('url_rewrites', $response['products']['items'][0]);
        $this->assertCount(1, $response['products']['items'][0]['url_rewrites']);

        $this->assertResponseFields(
            $response['products']['items'][0]['url_rewrites'][0],
            [
                "url" => $urlRewrite->getRequestPath(),
                "parameters" => $this->getUrlParameters($urlRewrite->getTargetPath())
            ]
        );
    }

    /**
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testProductWithOneCategoryAssigned()
    {
        $productSku = 'simple';
        $query
            = <<<QUERY
{
    products (filter: {sku: {eq: "{$productSku}"}}) {
        items {
            name,
            sku,
            description {
                html
            }
            url_rewrites {
              url,
              parameters {
                name,
                value
              }
            }
        }
    }
}
QUERY;

        $response = $this->graphQlQuery($query);

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        $product = $productRepository->get('simple', false, null, true);

        $storeId = ObjectManager::getInstance()->get(StoreManagerInterface::class)->getStore()->getId();
        $urlFinder = ObjectManager::getInstance()->get(UrlFinderInterface::class);
        $entityType = ObjectManager::getInstance()->create(EavConfig::class)->getEntityType('catalog_product');

        $entityTypeCode = $entityType->getEntityTypeCode();
        if ($entityTypeCode === 'catalog_product') {
            $entityTypeCode = 'product';
        }

        $rewritesCollection = $urlFinder->findAllByData(
            [
                UrlRewriteDTO::ENTITY_ID => $product->getId(),
                UrlRewriteDTO::ENTITY_TYPE => $entityTypeCode,
                UrlRewriteDTO::STORE_ID => $storeId
            ]
        );

        $rewritesCount = count($rewritesCollection);
        $this->assertArrayHasKey('url_rewrites', $response['products']['items'][0]);
        $this->assertCount(1, $response['products']['items'][0]['url_rewrites']);
        $this->assertCount($rewritesCount, $response['products']['items'][0]['url_rewrites']);

        for ($index = 0; $index < $rewritesCount; $index++) {
            $urlRewrite = $rewritesCollection[$index];
            $this->assertResponseFields(
                $response['products']['items'][0]['url_rewrites'][$index],
                [
                    "url" => $urlRewrite->getRequestPath(),
                    "parameters" => $this->getUrlParameters($urlRewrite->getTargetPath())
                ]
            );
        }
    }

    #[
        DataFixture(StoreFixture::class, as: 'store2'),
        DataFixture(CategoryFixture::class, ['url_key' => 'rewrite-category'], 'category'),
        DataFixture(
            ProductFixture::class,
            ['sku' => 'rewrite-one', 'url_key' => 'rewrite-one', 'category_ids' => ['$category.id$']],
            'p1'
        ),
        DataFixture(
            ProductFixture::class,
            ['sku' => 'rewrite-two', 'url_key' => 'rewrite-two', 'category_ids' => ['$category.id$']],
            'p2'
        ),
        DataFixture(ProductFixture::class, ['sku' => 'rewrite-three', 'url_key' => 'rewrite-three'], 'p3'),
        DataFixture(ProductFixture::class, ['sku' => 'rewrite-none', 'url_key' => 'rewrite-none'], 'p4'),
    ]
    public function testUrlRewritesOfSeveralProductsInOneQuery(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $objectManager = ObjectManager::getInstance();
        $objectManager->get(UrlPersistInterface::class)->deleteByData([
            UrlRewriteDTO::ENTITY_TYPE => 'product',
            UrlRewriteDTO::ENTITY_ID => (int)$fixtures->get('p4')->getId(),
        ]);
        $productId = (string)$fixtures->get('p1')->getId();
        $categoryId = (string)$fixtures->get('category')->getId();
        $query = <<<QUERY
{
  products(filter: {sku: {in: ["rewrite-one", "rewrite-two", "rewrite-three", "rewrite-none"]}}) {
    items {
      sku
      url_rewrites {
        url
        parameters {
          name
          value
        }
      }
    }
  }
}
QUERY;

        $storeRepository = $objectManager->get(StoreRepositoryInterface::class);
        foreach (['default', $fixtures->get('store2')->getCode()] as $storeCode) {
            $storeId = (int)$storeRepository->get($storeCode)->getId();
            $response = $this->graphQlQuery($query, [], '', ['Store' => $storeCode]);
            $items = array_column($response['products']['items'], 'url_rewrites', 'sku');

            $this->assertCount(4, $items);
            $this->assertEqualsCanonicalizing(
                [
                    ['url' => 'rewrite-one.html', 'parameters' => [['name' => 'id', 'value' => $productId]]],
                    [
                        'url' => 'rewrite-category/rewrite-one.html',
                        'parameters' => [
                            ['name' => 'id', 'value' => $productId],
                            ['name' => 'category', 'value' => $categoryId],
                        ],
                    ],
                ],
                $items['rewrite-one'],
                sprintf('url_rewrites of "rewrite-one" in store "%s"', $storeCode)
            );
            $this->assertSame([], $items['rewrite-none']);
            foreach (['p1', 'p2', 'p3', 'p4'] as $productKey) {
                $product = $fixtures->get($productKey);
                $this->assertEquals(
                    $this->getProductUrlRewrites((int)$product->getId(), $storeId),
                    $items[$product->getSku()],
                    sprintf('url_rewrites of "%s" in store "%s"', $product->getSku(), $storeCode)
                );
            }
        }
    }

    /**
     * Get url_rewrites of one product as a single-product lookup returns them
     *
     * @param int $productId
     * @param int $storeId
     * @return array
     */
    private function getProductUrlRewrites(int $productId, int $storeId): array
    {
        $urlRewrites = ObjectManager::getInstance()->get(UrlFinderInterface::class)->findAllByData([
            UrlRewriteDTO::ENTITY_TYPE => 'product',
            UrlRewriteDTO::ENTITY_ID => $productId,
            UrlRewriteDTO::STORE_ID => $storeId,
        ]);

        $expected = [];
        foreach ($urlRewrites as $urlRewrite) {
            if ($urlRewrite->getRedirectType() === 0) {
                $expected[] = [
                    'url' => $urlRewrite->getRequestPath(),
                    'parameters' => $this->getUrlParameters($urlRewrite->getTargetPath()),
                ];
            }
        }

        return $expected;
    }

    /**
     * Parses target path and extracts parameters
     *
     * @param string $targetPath
     * @return array
     */
    private function getUrlParameters(string $targetPath): array
    {
        $urlParameters = [];
        $targetPathParts = explode('/', trim($targetPath, '/'));
        $count = count($targetPathParts) - 1;
        //phpcs:ignore Generic.CodeAnalysis.ForLoopWithTestFunctionCall
        for ($index = 3; $index < $count; $index += 2) {
            $urlParameters[] = [
                'name' => $targetPathParts[$index],
                'value' => $targetPathParts[$index + 1]
            ];
        }

        return $urlParameters;
    }
}
