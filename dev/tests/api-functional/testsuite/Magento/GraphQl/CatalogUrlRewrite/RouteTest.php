<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\CatalogUrlRewrite;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Model\UrlRewrite;

/**
 * Test the GraphQL endpoint's route query to verify URL route information is correctly returned.
 */
class RouteTest extends GraphQlAbstract
{
    /** @var ObjectManager */
    private $objectManager;

    /** @var Uid */
    private $idDecoder;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->idDecoder = $this->objectManager->get(Uid::class);
    }

    /**
     * Tests if target_path(relative_url) is resolved for Product entity
     *
     * @magentoApiDataFixture Magento/CatalogUrlRewrite/_files/product_with_category.php
     */
    public function testProductUrlResolver()
    {
        $productSku = 'p002';
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($productSku, false, null, true);
        $storeId = $product->getStoreId();

        $query
            = <<<QUERY
{
    products(filter: {sku: {eq: "{$productSku}"}})
    {
        items {
               url_key
               url_suffix
            }
    }
}
QUERY;
        $response = $this->graphQlQuery($query);
        $urlPath = $response['products']['items'][0]['url_key'] . $response['products']['items'][0]['url_suffix'];

        /** @var  UrlFinderInterface $urlFinder */
        $urlFinder = $this->objectManager->get(UrlFinderInterface::class);
        $actualUrls = $urlFinder->findOneByData(
            [
                'request_path' => $urlPath,
                'store_id' => $storeId
            ]
        );
        $relativePath = $actualUrls->getRequestPath();
        $expectedType = $actualUrls->getEntityType();
        $redirectCode =  $actualUrls->getRedirectType();

        $this->queryUrlAndAssertResponse(
            (int) $product->getEntityId(),
            $urlPath,
            $relativePath,
            $expectedType,
            $redirectCode
        );
    }

    /**
     * Test the use case where non seo friendly is provided as resolver input in the Query
     *
     * @magentoApiDataFixture Magento/CatalogUrlRewrite/_files/product_with_category.php
     */
    public function testProductUrlWithNonSeoFriendlyUrlInput()
    {
        $productSku = 'p002';
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($productSku, false, null, true);
        $storeId = $product->getStoreId();

        $query
            = <<<QUERY
{
    products(filter: {sku: {eq: "{$productSku}"}})
    {
        items {
               url_key
               url_suffix
            }
    }
}
QUERY;
        $response = $this->graphQlQuery($query);
        $urlPath = $response['products']['items'][0]['url_key'] . $response['products']['items'][0]['url_suffix'];

        /** @var  UrlFinderInterface $urlFinder */
        $urlFinder = $this->objectManager->get(UrlFinderInterface::class);
        $actualUrls = $urlFinder->findOneByData(
            [
                'request_path' => $urlPath,
                'store_id' => $storeId
            ]
        );
        // even if non seo friendly path requested, the seo friendly path should be preferred
        $relativePath = $actualUrls->getRequestPath();
        $expectedType = $actualUrls->getEntityType();
        $nonSeoFriendlyPath = $actualUrls->getTargetPath();
        $redirectCode =  $actualUrls->getRedirectType();

        $this->queryUrlAndAssertResponse(
            (int) $product->getEntityId(),
            $nonSeoFriendlyPath,
            $relativePath,
            $expectedType,
            $redirectCode
        );
    }

    /**
     * Test the use case where non seo friendly is provided as resolver input in the Query
     *
     * @magentoApiDataFixture Magento/CatalogUrlRewrite/_files/product_with_category.php
     */
    public function testRedirectsAndCustomInput()
    {
        $productSku = 'p002';
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($productSku, false, null, true);

        // generate permanent redirects
        $renamedKey = 'p002-ren';
        $product->setUrlKey($renamedKey)->setData('save_rewrites_history', true)->save();

        $storeId = $product->getStoreId();

        $query
            = <<<QUERY
{
    products(filter: {sku: {eq: "{$productSku}"}})
    {
        items {
               url_key
               url_suffix
            }
    }
}
QUERY;
        $response = $this->graphQlQuery($query);
        $urlPath = $response['products']['items'][0]['url_key'] . $response['products']['items'][0]['url_suffix'];
        $suffix = $response['products']['items'][0]['url_suffix'];

        /** @var  UrlFinderInterface $urlFinder */
        $urlFinder = $this->objectManager->get(UrlFinderInterface::class);
        $actualUrls = $urlFinder->findOneByData(
            [
                'request_path' => $urlPath,
                'store_id' => $storeId
            ]
        );
        // querying the end redirect gives the same record
        $this->queryUrlAndAssertResponse(
            (int) $product->getEntityId(),
            $renamedKey . $suffix,
            $actualUrls->getRequestPath(),
            $actualUrls->getEntityType(),
            0
        );
        // querying a url that's a redirect the active redirected final url
        $this->queryUrlAndAssertResponse(
            (int) $product->getEntityId(),
            $productSku . $suffix,
            $actualUrls->getRequestPath(),
            $actualUrls->getEntityType(),
            301
        );
        // create custom url that doesn't redirect
        /** @var  UrlRewrite $urlRewriteModel */
        $urlRewriteModel = $this->objectManager->create(UrlRewrite::class);

        $customUrl = 'custom-path';
        $urlRewriteArray = [
            'entity_type' => 'custom',
            'entity_id' => '0',
            'request_path' => $customUrl,
            'target_path' => 'p002.html',
            'redirect_type' => '0',
            'store_id' => '1',
            'description' => '',
            'is_autogenerated' => '0',
            'metadata' => null,
        ];
        foreach ($urlRewriteArray as $key => $value) {
            $urlRewriteModel->setData($key, $value);
        }
        $urlRewriteModel->save();
        // querying a custom url that should return the target entity but relative should be the custom url
        $this->queryUrlAndAssertResponse(
            (int) $product->getEntityId(),
            $customUrl,
            $customUrl,
            $actualUrls->getEntityType(),
            0
        );
        // change custom url that does redirect
        $urlRewriteModel->setRedirectType('301');
        $urlRewriteModel->setId($urlRewriteModel->getId());
        $urlRewriteModel->save();
        //modifying query by adding spaces to avoid getting cached values.
        $this->queryUrlAndAssertResponse(
            (int) $product->getEntityId(),
            $customUrl . ' ',
            $actualUrls->getRequestPath(),
            strtoupper($actualUrls->getEntityType()),
            301
        );
        $urlRewriteModel->delete();
    }

    /**
     * Test for category entity
     *
     * @magentoApiDataFixture Magento/CatalogUrlRewrite/_files/product_with_category.php
     */
    public function testCategoryUrlResolver($categoryUrlPath = null)
    {
        $productSku = 'p002';
        $categoryUrlPath = $categoryUrlPath ? $categoryUrlPath : 'cat-1.html';
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($productSku, false, null, true);
        $storeId = $product->getStoreId();

        /** @var  UrlFinderInterface $urlFinder */
        $urlFinder = $this->objectManager->get(UrlFinderInterface::class);
        $actualUrls = $urlFinder->findOneByData(
            [
                'request_path' => $categoryUrlPath,
                'store_id' => $storeId
            ]
        );
        $categoryId = $actualUrls->getEntityId();
        $relativePath = $actualUrls->getRequestPath();
        $expectedType = $actualUrls->getEntityType();

        $query
            = <<<QUERY
{
    category(id:{$categoryId}) {
        url_key
        url_suffix
    }
}
QUERY;
        $response = $this->graphQlQuery($query);
        $urlPath = $response['category']['url_key'] . $response['category']['url_suffix'];

        $this->queryUrlAndAssertResponse(
            (int) $categoryId,
            $urlPath,
            $relativePath,
            $expectedType,
            0
        );
    }

    /**
     * Test the use case where the url_key of the existing product is changed
     *
     * @magentoApiDataFixture Magento/CatalogUrlRewrite/_files/product_with_category.php
     */
    public function testProductUrlRewriteResolver()
    {
        $productSku = 'p002';
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($productSku, false, null, true);
        $storeId = $product->getStoreId();
        $product->setUrlKey('p002-new')->save();

        $query
            = <<<QUERY
{
    products(filter: {sku: {eq: "{$productSku}"}})
    {
        items {
               url_key
               url_suffix
            }
    }
}
QUERY;
        $response = $this->graphQlQuery($query);
        $urlPath = $response['products']['items'][0]['url_key'] . $response['products']['items'][0]['url_suffix'];

        $this->assertEquals($urlPath, 'p002-new' . $response['products']['items'][0]['url_suffix']);

        /** @var  UrlFinderInterface $urlFinder */
        $urlFinder = $this->objectManager->get(UrlFinderInterface::class);
        $actualUrls = $urlFinder->findOneByData(
            [
                'request_path' => $urlPath,
                'store_id' => $storeId
            ]
        );
        $relativePath = $actualUrls->getRequestPath();
        $expectedType = $actualUrls->getEntityType();

        $this->queryUrlAndAssertResponse(
            (int) $product->getEntityId(),
            $urlPath,
            $relativePath,
            $expectedType,
            0
        );
    }

    /**
     * Tests if null is returned when an invalid request_path is provided as input to urlResolver
     *
     * @magentoApiDataFixture Magento/CatalogUrlRewrite/_files/product_with_category.php
     */
    public function testInvalidUrlResolverInput()
    {
        $productSku = 'p002';
        $urlPath = 'p002';
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($productSku, false, null, true);
        $storeId = $product->getStoreId();

        /** @var  UrlFinderInterface $urlFinder */
        $urlFinder = $this->objectManager->get(UrlFinderInterface::class);
        $urlFinder->findOneByData(
            [
                'request_path' => $urlPath,
                'store_id' => $storeId
            ]
        );
        $query
            = <<<QUERY
{
  route(url:"{$urlPath}")
  {
   relative_url
   type
   redirect_code
  }
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('route', $response);
        $this->assertNull($response['route']);
    }

    /**
     * Test for category entity with leading slash
     *
     * @magentoApiDataFixture Magento/CatalogUrlRewrite/_files/product_with_category.php
     */
    public function testCategoryUrlWithLeadingSlash()
    {
        $productSku = 'p002';
        $categoryUrlPath = 'cat-1.html';
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($productSku, false, null, true);
        $storeId = $product->getStoreId();

        /** @var  UrlFinderInterface $urlFinder */
        $urlFinder = $this->objectManager->get(UrlFinderInterface::class);
        $actualUrls = $urlFinder->findOneByData(
            [
                'request_path' => $categoryUrlPath,
                'store_id' => $storeId
            ]
        );
        $categoryId = $actualUrls->getEntityId();
        $relativePath = $actualUrls->getRequestPath();
        $expectedType = $actualUrls->getEntityType();

        $query
            = <<<QUERY
{
    category(id:{$categoryId}) {
        url_key
        url_suffix
    }
}
QUERY;
        $response = $this->graphQlQuery($query);
        $urlPath = $response['category']['url_key'] . $response['category']['url_suffix'];
        $urlPathWithLeadingSlash = "/{$urlPath}";
        $this->queryUrlAndAssertResponse(
            (int) $categoryId,
            $urlPathWithLeadingSlash,
            $relativePath,
            $expectedType,
            0
        );
    }

    /**
     * Test for custom type which point to the valid product/category/cms page.
     *
     * @magentoApiDataFixture Magento/CatalogUrlRewrite/_files/product_with_category.php
     */
    public function testGetNonExistentUrlRewrite()
    {
        $urlPath = 'non-exist-product.html';
        /** @var UrlRewrite $urlRewrite */
        $urlRewrite = $this->objectManager->create(UrlRewrite::class);
        $urlRewrite->load($urlPath, 'request_path');

        /** @var  UrlFinderInterface $urlFinder */
        $urlFinder = $this->objectManager->get(UrlFinderInterface::class);
        $actualUrls = $urlFinder->findOneByData(
            [
                'request_path' => $urlPath,
                'store_id' => 1
            ]
        );
        $relativePath = $actualUrls->getRequestPath();

        $query = <<<QUERY
{
  route(url:"{$urlPath}")
  {
   relative_url
   type
   redirect_code
  }
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('route', $response);
        $this->assertEquals('PRODUCT', $response['route']['type']);
        $this->assertEquals($relativePath, $response['route']['relative_url']);
        $this->assertEquals(0, $response['route']['redirect_code']);
    }

    /**
     * Test for category entity with empty url suffix
     *
     * @magentoApiDataFixture Magento/CatalogUrlRewrite/_files/product_with_category_empty_url_suffix.php
     */
    public function testCategoryUrlResolverWithEmptyUrlSuffix()
    {
        $this->testCategoryUrlResolver('cat-1');
    }

    /**
     * Tests if target_path(relative_url) is resolved for Product entity with empty url suffix
     *
     * @magentoApiDataFixture Magento/CatalogUrlRewrite/_files/product_with_category_empty_url_suffix.php
     */
    public function testProductUrlResolverWithEmptyUrlSuffix()
    {
        $this->testProductUrlResolver();
    }

    /**
     * Assert response from GraphQl
     *
     * @param string $productId
     * @param string $urlKey
     * @param string $relativePath
     * @param string $expectedType
     * @param int $redirectCode
     * @throws GraphQlInputException
     */
    private function queryUrlAndAssertResponse(
        int $productId,
        string $urlKey,
        string $relativePath,
        string $expectedType,
        int $redirectCode
    ): void {
        $query
            = <<<QUERY
{
  route(url:"{$urlKey}")
  {
       relative_url
       type
       redirect_code
       __typename
         ...on SimpleProduct {
          uid
        }
         ...on ConfigurableProduct {
          uid
        }
        ...on CategoryTree {
            uid
        }
    }
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('route', $response);
        $this->assertEquals($productId, $this->idDecoder->decode($response['route']['uid']));
        $this->assertEquals($relativePath, $response['route']['relative_url']);
        $this->assertEquals(strtoupper($expectedType), $response['route']['type']);
        $this->assertEquals($redirectCode, $response['route']['redirect_code']);
    }
}
