<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\UrlRewrite;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Model\UrlRewrite;

/**
 * Test the GraphQL endpoint's URLResolver query to verify url route information is correctly returned.
 */
class RouteTest extends GraphQlAbstract
{
    /** @var ObjectManager */
    private $objectManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
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

        $response = $this->getRouteQueryResponse($this->getProductUrlKey($productSku));
        $this->productTestAssertion($product, $response);
        $expectedUrls = $this->getProductUrlRewriteData($productSku);
        $this->assertEquals($expectedUrls->getRequestPath(), $response['route']['relative_url']);
        $this->assertEquals($expectedUrls->getRedirectType(), $response['route']['redirect_code']);
        $this->assertEquals(strtoupper($expectedUrls->getEntityType()), $response['route']['type']);
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

        $actualUrls = $this->getProductUrlRewriteData($productSku);
        $nonSeoFriendlyPath = $actualUrls->getTargetPath();

        $response = $this->getRouteQueryResponse($nonSeoFriendlyPath);
        $this->productTestAssertion($product, $response);
    }

    /**
     * Test the use case where url_key of the existing product is changed and verify final url is redirected correctly
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_with_category.php
     */
    public function testProductUrlRewriteResolver()
    {
        $productSku = 'in-stock-product';
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($productSku, false, null, true);
        $initialUrlPath = $this->getProductUrlKey($productSku);
        $renamedKey = 'simple-product-in-stock-new';
        $suffix = '.html';
        $product->setUrlKey($renamedKey)->setData('save_rewrites_history', true)->save();
        $newUrlPath = $renamedKey . $suffix;

        $response = $this->getRouteQueryResponse($newUrlPath);
        $this->productTestAssertion($product, $response);

        $expectedUrls = $this->getProductUrlRewriteData($productSku);
        $this->assertEquals($expectedUrls->getRequestPath(), $response['route']['relative_url']);
        $this->assertEquals($expectedUrls->getRedirectType(), $response['route']['redirect_code']);
        $this->assertEquals(strtoupper($expectedUrls->getEntityType()), $response['route']['type']);

        // verify that product url is redirected to the final url with the correct redirectType
        $response = $this->getRouteQueryResponse($initialUrlPath);
        $this->assertEquals('simple-product-in-stock-new.html', $response['route']['relative_url']);
        $this->assertEquals(301, $response['route']['redirect_code']);
    }

    /**
     * Test for custom type which point to the valid product/category/cms page.
     *
     * @magentoApiDataFixture Magento/CatalogUrlRewrite/_files/product_with_category.php
     */
    public function testGetNonExistentUrlRewrite()
    {
        $productSku = 'p002';
        $urlPath = 'non-exist-product.html';

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($productSku, false, null, true);

        /** @var UrlRewrite $urlRewrite */
        $urlRewrite = $this->objectManager->create(UrlRewrite::class);
        $urlRewrite->load($urlPath, 'request_path');

        $response = $this->getRouteQueryResponse($urlPath);
        $this->assertNotNull($response['route']);
        $this->productTestAssertion($product, $response);
        $this->assertEquals($urlPath, $response['route']['relative_url']);
        $this->assertEquals(0, $response['route']['redirect_code']);
        $this->assertEquals('PRODUCT', $response['route']['type']);
    }

    /**
     * Test for category entity
     *
     * @magentoApiDataFixture Magento/CatalogUrlRewrite/_files/product_with_category.php
     */
    public function testCategoryUrlResolver()
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
        $categoryRepository = $this->objectManager->get(CategoryRepositoryInterface::class);
        $category = $categoryRepository->get($categoryId);

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
        $response = $this->getRouteQueryResponse($urlPath);

        $this->assertArrayHasKey('route', $response);
        $this->assertEquals($category->getName(), $response['route']['name']);
        $this->assertEquals(base64_encode($category->getId()), $response['route']['uid']);
        $this->assertEquals($urlPath, $response['route']['relative_url']);
        $this->assertEquals(0, $response['route']['redirect_code']);
        $this->assertEquals('CATEGORY', $response['route']['type']);
    }

    /**
     * @magentoApiDataFixture Magento/Cms/_files/pages.php
     */
    public function testCMSPageUrlResolver()
    {
        /** @var \Magento\Cms\Model\Page $page */
        $page = $this->objectManager->get(\Magento\Cms\Model\Page::class);
        $page->load('page100');
        $cmsPageData = $page->getData();

        /** @var \Magento\CmsUrlRewrite\Model\CmsPageUrlPathGenerator $urlPathGenerator */
        $urlPathGenerator = $this->objectManager->get(\Magento\CmsUrlRewrite\Model\CmsPageUrlPathGenerator::class);

        /** @param \Magento\Cms\Api\Data\PageInterface $page */
        $targetPath = $urlPathGenerator->getCanonicalUrlPath($page);

        $response = $this->getRouteQueryResponse($targetPath);

        $urlPath = $urlPathGenerator->getUrlPath($page);

        $this->assertArrayHasKey('route', $response);
        $this->assertEquals($cmsPageData['identifier'], $response['route']['url_key']);
        $this->assertEquals($cmsPageData['title'], $response['route']['title']);
        $this->assertEquals($cmsPageData['content'], $response['route']['content']);
        $this->assertEquals($cmsPageData['content_heading'], $response['route']['content_heading']);
        $this->assertEquals($cmsPageData['page_layout'], $response['route']['page_layout']);
        $this->assertEquals($urlPath, $response['route']['relative_url']);
        $this->assertEquals(0, $response['route']['redirect_code']);
        $this->assertEquals('CMS_PAGE', $response['route']['type']);
    }

    /**
     * @param string $urlKey
     * @return array
     * @throws \Exception
     */
    public function getRouteQueryResponse(string $urlKey): array
    {
        $routeQuery
            = <<<QUERY
{
  route(url:"{$urlKey}")
  {
    __typename
    ...on SimpleProduct {
      name
      sku
      relative_url
      redirect_code
      type
    }
    ...on CategoryTree {
        name
        uid
        relative_url
        redirect_code
        type
    }
    ...on CmsPage {
    	title
        url_key
        page_layout
        content
        content_heading
        relative_url
        redirect_code
        type
    }
  }
}
QUERY;
        return $this->graphQlQuery($routeQuery);
    }

    /**
     * @param string $productSku
     * @return string
     * @throws \Exception
     */
    public function getProductUrlKey(string $productSku): string
    {
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
        return $response['products']['items'][0]['url_key'] . $response['products']['items'][0]['url_suffix'];
    }

    /**
     * @param ProductInterface $product
     * @param array $response
     */
    private function productTestAssertion(ProductInterface $product, array $response)
    {
        $this->assertArrayHasKey('route', $response);
        $this->assertEquals($product->getName(), $response['route']['name']);
        $this->assertEquals($product->getSku(), $response['route']['sku']);
    }

    /**
     * @param $productSku
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getProductUrlRewriteData($productSku): \Magento\UrlRewrite\Service\V1\Data\UrlRewrite
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($productSku, false, null, true);
        $storeId = $product->getStoreId();

        $urlPath = $this->getProductUrlKey($productSku);

        /** @var  UrlFinderInterface $urlFinder */
        $urlFinder = $this->objectManager->get(UrlFinderInterface::class);
        /** @var \Magento\UrlRewrite\Service\V1\Data\UrlRewrite $actualUrls */
        $actualUrls = $urlFinder->findOneByData(
            [
                'request_path' => $urlPath,
                'store_id' => $storeId
            ]
        );
         return $actualUrls;
    }
}
