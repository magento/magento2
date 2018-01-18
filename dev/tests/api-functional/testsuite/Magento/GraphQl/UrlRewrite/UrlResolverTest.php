<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GraphQl\UrlRewrite;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

class UrlResolverTest extends GraphQlAbstract
{

    /** @var  ObjectManager */
    private $objectManager;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator
     */
    private $urlPathGenerator;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * Tests if the target_path(canonical_url) when a Product entity with a valid url_key (request_path) is provided
     *
     *  @magentoApiDataFixture Magento/CatalogUrlRewrite/_files/product_with_category.php
     */
    public function testProductUrlResolver()
    {
        $productSku = 'p002';
        $urlPath = 'p002.html';
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($productSku, false, null, true);
        $storeId  = $product->getStoreId();

        /** @var  UrlFinderInterface $urlFinder */
        $urlFinder = $this->objectManager->get(UrlFinderInterface::class);
        $actualUrls = $urlFinder->findOneByData(
            [
                'request_path' =>$urlPath,
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
   canonical_url
   type
  }
    
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('urlResolver', $response);
        $this->assertEquals($product->getEntityId(), $response['urlResolver']['id']);
        $this->assertEquals($targetPath, $response['urlResolver']['canonical_url']);
        $this->assertEquals(strtoupper($expectedType), $response['urlResolver']['type']);
    }

    /**
     *  @magentoApiDataFixture Magento/CatalogUrlRewrite/_files/product_with_category.php
     */
    public function testProductUrlWithCanonicalUrlInput()
    {
        $productSku = 'p002';
        $urlPath = 'p002.html';
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($productSku, false, null, true);
        $storeId  = $product->getStoreId();
        $product->getUrlKey();

        /** @var  UrlFinderInterface $urlFinder */
        $urlFinder = $this->objectManager->get(UrlFinderInterface::class);
        $actualUrls = $urlFinder->findOneByData(
            [
                'request_path' =>$urlPath,
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
   canonical_url
   type
  }
    
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('urlResolver', $response);
        $this->assertEquals($product->getEntityId(), $response['urlResolver']['id']);
        $this->assertEquals($targetPath, $response['urlResolver']['canonical_url']);
        $this->assertEquals(strtoupper($expectedType), $response['urlResolver']['type']);
    }

    /**
     *Test the
     * @magentoApiDataFixture Magento/CatalogUrlRewrite/_files/product_with_category.php
     */
    public function testCategoryUrlResolver()
    {
        $productSku = 'p002';
        $urlPath2 = 'cat-1.html';
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($productSku, false, null, true);
        $storeId  = $product->getStoreId();

        /** @var  UrlFinderInterface $urlFinder */
        $urlFinder = $this->objectManager->get(UrlFinderInterface::class);
        $actualUrls = $urlFinder->findOneByData(
            [
                'request_path' =>$urlPath2,
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
   canonical_url
   type
  }
    
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('urlResolver', $response);
        $this->assertEquals($categoryId, $response['urlResolver']['id']);
        $this->assertEquals($targetPath, $response['urlResolver']['canonical_url']);
        $this->assertEquals(strtoupper($expectedType), $response['urlResolver']['type']);
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
        $storeId  = $product->getStoreId();
        $product->setUrlKey('p002-new')->save();
        $urlPath = $product->getUrlKey() .'.html';
        $this->assertEquals($urlPath, 'p002-new.html');

        /** @var  UrlFinderInterface $urlFinder */
        $urlFinder = $this->objectManager->get(UrlFinderInterface::class);
        $actualUrls = $urlFinder->findOneByData(
            [
                'request_path' =>$urlPath,
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
   canonical_url
   type
  }
    
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('urlResolver', $response);
        $this->assertEquals($product->getEntityId(), $response['urlResolver']['id']);
        $this->assertEquals($targetPath, $response['urlResolver']['canonical_url']);
        $this->assertEquals(strtoupper($expectedType), $response['urlResolver']['type']);
    }

    /**
     *  Tests if null is returned when an invalid request_path is provided as input to urlResolver
     *
     *  @magentoApiDataFixture Magento/CatalogUrlRewrite/_files/product_with_category.php
     */
    public function testInvalidUrlResolverInput()
    {
        $productSku = 'p002';
        $urlPath = 'p002';
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($productSku, false, null, true);
        $storeId  = $product->getStoreId();

        /** @var  UrlFinderInterface $urlFinder */
        $urlFinder = $this->objectManager->get(UrlFinderInterface::class);
        $urlFinder->findOneByData(
            [
                'request_path' =>$urlPath,
                'store_id' => $storeId
            ]
        );
        $query
            = <<<QUERY
{
  urlResolver(url:"{$urlPath}")
  {
   id
   canonical_url
   type
  }
    
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('urlResolver', $response);
        $this->assertNull($response['urlResolver']);
    }
}
