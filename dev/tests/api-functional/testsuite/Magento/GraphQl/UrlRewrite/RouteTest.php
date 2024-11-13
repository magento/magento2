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
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewrite as UrlRewriteResourceModel;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Model\UrlRewrite as UrlRewriteModel;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite as UrlRewriteService;
use Magento\UrlRewrite\Test\Fixture\UrlRewrite as UrlRewriteFixture;

/**
 * Test the GraphQL endpoint's Route query to verify url route information is correctly returned.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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

        /** @var UrlRewriteModel $urlRewriteModel */
        $urlRewriteModel = $this->objectManager->create(UrlRewriteModel::class);
        $urlRewriteModel->load($urlPath, 'request_path');

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
    relative_url
    redirect_code
    type
    ...on SimpleProduct {
        name
        sku
    }
    ...on CategoryTree {
        name
        uid
    }
    ...on CmsPage {
    	title
        url_key
        page_layout
        content
        content_heading
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
     * @return UrlRewriteService
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getProductUrlRewriteData($productSku): UrlRewriteService
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($productSku, false, null, true);
        $storeId = $product->getStoreId();

        $urlPath = $this->getProductUrlKey($productSku);

        /** @var  UrlFinderInterface $urlFinder */
        $urlFinder = $this->objectManager->get(UrlFinderInterface::class);
        /** @var UrlRewriteService $actualUrls */
        $actualUrls = $urlFinder->findOneByData(
            [
                'request_path' => $urlPath,
                'store_id' => $storeId
            ]
        );
        return $actualUrls;
    }

    /**
     * Test for custom type which point to the invalid product/category/cms page.
     *
     * @magentoApiDataFixture Magento/UrlRewrite/_files/url_rewrite_not_existing_entity.php
     */
    public function testNonExistentEntityUrlRewrite()
    {
        $urlPath = 'non-exist-entity.html';

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

        $this->expectExceptionMessage(
            "No such entity found with matching URL key: " . $urlPath
        );
        $this->graphQlQuery($query);
    }

    /**
     * Test for url rewrite to clean cache on rewrites update
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_with_category.php
     * @magentoApiDataFixture Magento/Cms/_files/pages.php
     *
     * @dataProvider urlRewriteEntitiesDataProvider
     * @param string $requestPath
     * @throws AlreadyExistsException
     */
    public function testUrlRewriteCleansCacheOnChange(string $requestPath)
    {

        /** @var UrlRewriteResourceModel $urlRewriteResourceModel */
        $urlRewriteResourceModel = $this->objectManager->create(UrlRewriteResourceModel::class);
        $storeId = 1;
        $query = function ($requestUrl) {
            return <<<QUERY
{
  route(url:"{$requestUrl}")
  {
   relative_url
   type
   redirect_code
  }
}
QUERY;
        };

        // warming up route API response cache for entity and validate proper response
        $apiResponse = $this->graphQlQuery($query($requestPath));
        $this->assertEquals($requestPath, $apiResponse['route']['relative_url']);

        $urlRewrite = $this->getUrlRewriteModelByRequestPath($requestPath, $storeId);

        // renaming entity request path and validating that API will not return cached response
        $urlRewrite->setRequestPath('test' . $requestPath);
        $urlRewriteResourceModel->save($urlRewrite);
        $apiResponse = $this->graphQlQuery($query($requestPath));
        $this->assertNull($apiResponse['route']);

        // rolling back changes
        $urlRewrite->setRequestPath($requestPath);
        $urlRewriteResourceModel->save($urlRewrite);
    }

    public static function urlRewriteEntitiesDataProvider(): array
    {
        return [
            [
                'simple-product-in-stock.html'
            ],
            [
                'category-1.html'
            ],
            [
                'page100'
            ]
        ];
    }

    /**
     * Test for custom url rewrite to clean cache on update combinations
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_with_category.php
     * @magentoApiDataFixture Magento/Cms/_files/pages.php
     *
     * @throws AlreadyExistsException
     */
    public function testUrlRewriteCleansCacheForCustomRewrites()
    {

        /** @var UrlRewriteResourceModel $urlRewriteResourceModel */
        $urlRewriteResourceModel = $this->objectManager->create(UrlRewriteResourceModel::class);
        $storeId = 1;
        $query = function ($requestUrl) {
            return <<<QUERY
{
  route(url:"{$requestUrl}")
  {
   relative_url
   type
   redirect_code
  }
}
QUERY;
        };

        $customRequestPath = 'test.html';
        $customSecondRequestPath = 'test2.html';
        $entitiesRequestPaths = [
            'simple-product-in-stock.html',
            'category-1.html',
            'page100'
        ];

        // create custom url rewrite
        $urlRewriteModel = $this->objectManager->create(UrlRewriteModel::class);
        $urlRewriteModel->setEntityType('custom')
            ->setRedirectType(302)
            ->setStoreId($storeId)
            ->setDescription(null)
            ->setIsAutogenerated(0);

        // create second custom url rewrite and target it to previous one to check
        // if proper final target url will be resolved
        $secondUrlRewriteModel = $this->objectManager->create(UrlRewriteModel::class);
        $secondUrlRewriteModel->setEntityType('custom')
            ->setRedirectType(302)
            ->setStoreId($storeId)
            ->setRequestPath($customSecondRequestPath)
            ->setTargetPath($customRequestPath)
            ->setDescription(null)
            ->setIsAutogenerated(0);
        $urlRewriteResourceModel->save($secondUrlRewriteModel);

        foreach ($entitiesRequestPaths as $entityRequestPath) {
            // updating custom rewrite for each entity
            $urlRewriteModel->setRequestPath($customRequestPath)
                ->setTargetPath($entityRequestPath);
            $urlRewriteResourceModel->save($urlRewriteModel);

            // confirm that API returns non-cached response for the first custom rewrite
            $apiResponse = $this->graphQlQuery($query($customRequestPath));
            $this->assertEquals($entityRequestPath, $apiResponse['route']['relative_url']);

            // confirm that API returns non-cached response for the second custom rewrite
            $apiResponse = $this->graphQlQuery($query($customSecondRequestPath));
            $this->assertEquals($entityRequestPath, $apiResponse['route']['relative_url']);
        }

        $urlRewriteResourceModel->delete($secondUrlRewriteModel);

        // delete custom rewrite and validate that API will not return cached response
        $urlRewriteResourceModel->delete($urlRewriteModel);
        $apiResponse = $this->graphQlQuery($query($customRequestPath));
        $this->assertNull($apiResponse['route']);
    }

    #[
        DataFixture(UrlRewriteFixture::class, ['redirect_type' => 301, 'target_path' => 'http://example.com'], 'url')
    ]
    public function testCustomUrlRewriteRedirectToExternalUrl(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $urlRewrite = $fixtures->get('url');
        $response = $this->getRouteQueryResponse($urlRewrite->getRequestPath());
        $this->assertNotNull($response['route']);
        $this->assertEquals('RoutableUrl', $response['route']['__typename']);
        $this->assertEquals($urlRewrite->getTargetPath(), $response['route']['relative_url']);
        $this->assertEquals($urlRewrite->getRedirectType(), $response['route']['redirect_code']);
        $this->assertNull($response['route']['type']);
    }

    /**
     * Return UrlRewrite model instance by request_path
     *
     * @param string $requestPath
     * @param int $storeId
     * @return UrlRewriteModel
     */
    private function getUrlRewriteModelByRequestPath(string $requestPath, int $storeId): UrlRewriteModel
    {
        /** @var  UrlFinderInterface $urlFinder */
        $urlFinder = $this->objectManager->get(UrlFinderInterface::class);

        /** @var UrlRewriteService $urlRewriteService */
        $urlRewriteService = $urlFinder->findOneByData(
            [
                'request_path' => $requestPath,
                'store_id' => $storeId
            ]
        );

        /** @var UrlRewriteModel $urlRewrite */
        $urlRewrite = $this->objectManager->create(UrlRewriteModel::class);
        $urlRewrite->load($urlRewriteService->getUrlRewriteId());

        return $urlRewrite;
    }
}
