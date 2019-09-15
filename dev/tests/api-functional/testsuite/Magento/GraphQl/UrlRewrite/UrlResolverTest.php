<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\UrlRewrite;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CmsUrlRewrite\Model\CmsPageUrlRewriteGenerator;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\Cms\Helper\Page as PageHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\UrlRewrite\Model\UrlRewrite;

/**
 * Test the GraphQL endpoint's URLResolver query to verify canonical URL's are correctly returned.
 */
class UrlResolverTest extends GraphQlAbstract
{

    /** @var  ObjectManager */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * Tests if target_path(relative_url) is resolved for Product entity
     *
     * @magentoApiDataFixture Magento/CatalogUrlRewrite/_files/product_with_category.php
     */
    public function testProductUrlResolver()
    {
        $productSku = 'p002';
        $urlPath = 'p002.html';
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($productSku, false, null, true);
        $storeId = $product->getStoreId();

        /** @var  UrlFinderInterface $urlFinder */
        $urlFinder = $this->objectManager->get(UrlFinderInterface::class);
        $actualUrls = $urlFinder->findOneByData(
            [
                'request_path' => $urlPath,
                'store_id' => $storeId
            ]
        );
        $targetPath = $actualUrls->getTargetPath();
        $expectedType = $actualUrls->getEntityType();
        $query
            = <<<QUERY
{
  urlResolver(url:"{$urlPath}")
  {
   id
   relative_url
   type
  }
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('urlResolver', $response);
        $this->assertEquals($product->getEntityId(), $response['urlResolver']['id']);
        $this->assertEquals($targetPath, $response['urlResolver']['relative_url']);
        $this->assertEquals(strtoupper($expectedType), $response['urlResolver']['type']);
    }

    /**
     * Tests the use case where relative_url is provided as resolver input in the Query
     *
     * @magentoApiDataFixture Magento/CatalogUrlRewrite/_files/product_with_category.php
     */
    public function testProductUrlWithCanonicalUrlInput()
    {
        $productSku = 'p002';
        $urlPath = 'p002.html';
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($productSku, false, null, true);
        $storeId = $product->getStoreId();
        $product->getUrlKey();

        /** @var  UrlFinderInterface $urlFinder */
        $urlFinder = $this->objectManager->get(UrlFinderInterface::class);
        $actualUrls = $urlFinder->findOneByData(
            [
                'request_path' => $urlPath,
                'store_id' => $storeId
            ]
        );
        $targetPath = $actualUrls->getTargetPath();
        $expectedType = $actualUrls->getEntityType();
        $canonicalPath = $actualUrls->getTargetPath();
        $query
            = <<<QUERY
{
  urlResolver(url:"{$canonicalPath}")
  {
   id
   relative_url
   type
  }
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('urlResolver', $response);
        $this->assertEquals($product->getEntityId(), $response['urlResolver']['id']);
        $this->assertEquals($targetPath, $response['urlResolver']['relative_url']);
        $this->assertEquals(strtoupper($expectedType), $response['urlResolver']['type']);
    }

    /**
     * Test for category entity
     *
     * @magentoApiDataFixture Magento/CatalogUrlRewrite/_files/product_with_category.php
     */
    public function testCategoryUrlResolver()
    {
        $productSku = 'p002';
        $urlPath2 = 'cat-1.html';
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($productSku, false, null, true);
        $storeId = $product->getStoreId();

        /** @var  UrlFinderInterface $urlFinder */
        $urlFinder = $this->objectManager->get(UrlFinderInterface::class);
        $actualUrls = $urlFinder->findOneByData(
            [
                'request_path' => $urlPath2,
                'store_id' => $storeId
            ]
        );
        $categoryId = $actualUrls->getEntityId();
        $targetPath = $actualUrls->getTargetPath();
        $expectedType = $actualUrls->getEntityType();
        $query
            = <<<QUERY
{
  urlResolver(url:"{$urlPath2}")
  {
   id
   relative_url
   type
  }
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('urlResolver', $response);
        $this->assertEquals($categoryId, $response['urlResolver']['id']);
        $this->assertEquals($targetPath, $response['urlResolver']['relative_url']);
        $this->assertEquals(strtoupper($expectedType), $response['urlResolver']['type']);
    }

    /**
     * @magentoApiDataFixture Magento/Cms/_files/pages.php
     */
    public function testCMSPageUrlResolver()
    {
        /** @var \Magento\Cms\Model\Page $page */
        $page = $this->objectManager->get(\Magento\Cms\Model\Page::class);
        $page->load('page100');
        $cmsPageId = $page->getId();
        $requestPath = $page->getIdentifier();

        /** @var \Magento\CmsUrlRewrite\Model\CmsPageUrlPathGenerator $urlPathGenerator */
        $urlPathGenerator = $this->objectManager->get(\Magento\CmsUrlRewrite\Model\CmsPageUrlPathGenerator::class);

        /** @param \Magento\Cms\Api\Data\PageInterface $page */
        $targetPath = $urlPathGenerator->getCanonicalUrlPath($page);
        $expectedEntityType = CmsPageUrlRewriteGenerator::ENTITY_TYPE;

        $query
            = <<<QUERY
{
  urlResolver(url:"{$requestPath}")
  {
   id
   relative_url
   type
  }
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertEquals($cmsPageId, $response['urlResolver']['id']);
        $this->assertEquals($targetPath, $response['urlResolver']['relative_url']);
        $this->assertEquals(strtoupper(str_replace('-', '_', $expectedEntityType)), $response['urlResolver']['type']);
    }

    /**
     * Tests the use case where the url_key of the existing product is changed
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
        $urlPath = $product->getUrlKey() . '.html';
        $this->assertEquals($urlPath, 'p002-new.html');

        /** @var  UrlFinderInterface $urlFinder */
        $urlFinder = $this->objectManager->get(UrlFinderInterface::class);
        $actualUrls = $urlFinder->findOneByData(
            [
                'request_path' => $urlPath,
                'store_id' => $storeId
            ]
        );
        $targetPath = $actualUrls->getTargetPath();
        $expectedType = $actualUrls->getEntityType();
        $query
            = <<<QUERY
{
  urlResolver(url:"{$urlPath}")
  {
   id
   relative_url
   type
  }
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('urlResolver', $response);
        $this->assertEquals($product->getEntityId(), $response['urlResolver']['id']);
        $this->assertEquals($targetPath, $response['urlResolver']['relative_url']);
        $this->assertEquals(strtoupper($expectedType), $response['urlResolver']['type']);
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
  urlResolver(url:"{$urlPath}")
  {
   id
   relative_url
   type
  }
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('urlResolver', $response);
        $this->assertNull($response['urlResolver']);
    }

    /**
     * Test for category entity with leading slash
     *
     * @magentoApiDataFixture Magento/CatalogUrlRewrite/_files/product_with_category.php
     */
    public function testCategoryUrlWithLeadingSlash()
    {
        $productSku = 'p002';
        $urlPath = 'cat-1.html';
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($productSku, false, null, true);
        $storeId = $product->getStoreId();

        /** @var  UrlFinderInterface $urlFinder */
        $urlFinder = $this->objectManager->get(UrlFinderInterface::class);
        $actualUrls = $urlFinder->findOneByData(
            [
                'request_path' => $urlPath,
                'store_id' => $storeId
            ]
        );
        $categoryId = $actualUrls->getEntityId();
        $targetPath = $actualUrls->getTargetPath();
        $expectedType = $actualUrls->getEntityType();

        $query = <<<QUERY
{
  urlResolver(url:"/{$urlPath}")
  {
   id
   relative_url
   type
  }
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('urlResolver', $response);
        $this->assertEquals($categoryId, $response['urlResolver']['id']);
        $this->assertEquals($targetPath, $response['urlResolver']['relative_url']);
        $this->assertEquals(strtoupper($expectedType), $response['urlResolver']['type']);
    }

    /**
     * Test resolution of '/' path to home page
     */
    public function testResolveSlash()
    {
        /** @var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface */
        $scopeConfigInterface = $this->objectManager->get(ScopeConfigInterface::class);
        $homePageIdentifier = $scopeConfigInterface->getValue(
            PageHelper::XML_PATH_HOME_PAGE,
            ScopeInterface::SCOPE_STORE
        );
        /** @var \Magento\Cms\Model\Page $page */
        $page = $this->objectManager->get(\Magento\Cms\Model\Page::class);
        $page->load($homePageIdentifier);
        $homePageId = $page->getId();
        /** @var \Magento\CmsUrlRewrite\Model\CmsPageUrlPathGenerator $urlPathGenerator */
        $urlPathGenerator = $this->objectManager->get(\Magento\CmsUrlRewrite\Model\CmsPageUrlPathGenerator::class);
        /** @param \Magento\Cms\Api\Data\PageInterface $page */
        $targetPath = $urlPathGenerator->getCanonicalUrlPath($page);
        $query
            = <<<QUERY
{
  urlResolver(url:"/")
  {
   id
   relative_url
   type
  }
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('urlResolver', $response);
        $this->assertEquals($homePageId, $response['urlResolver']['id']);
        $this->assertEquals($targetPath, $response['urlResolver']['relative_url']);
        $this->assertEquals('CMS_PAGE', $response['urlResolver']['type']);
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
        $targetPath = $actualUrls->getTargetPath();

        $query = <<<QUERY
{
  urlResolver(url:"{$urlPath}")
  {
   id
   relative_url
   type
  }
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('urlResolver', $response);
        $this->assertEquals('PRODUCT', $response['urlResolver']['type']);
        $this->assertEquals($targetPath, $response['urlResolver']['relative_url']);
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
  urlResolver(url:"{$urlPath}")
  {
   id
   relative_url
   type
  }
}
QUERY;

        $this->expectExceptionMessage(
            "No such entity found with matching URL key: " . $urlPath
        );
        $this->graphQlQuery($query);
    }
}
