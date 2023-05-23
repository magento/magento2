<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalg\ResolverCache;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Gallery\GalleryManagement;
use Magento\CatalogGraphQl\Model\Resolver\Cache\Product\MediaGallery\ResolverCacheIdentity;
use Magento\CatalogGraphQl\Model\Resolver\Product\MediaGallery;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\GraphQlResolverCache\Model\Resolver\Result\CacheKey\Calculator\ProviderInterface;
use Magento\GraphQlResolverCache\Model\Resolver\Result\Type as GraphQlResolverCache;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQl\ResolverCacheAbstract;

/**
 * Test for \Magento\CatalogGraphQl\Model\Resolver\Product\MediaGallery resolver cache
 */
class MediaGalleryTest extends ResolverCacheAbstract
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var GraphQlResolverCache
     */
    private $graphQlResolverCache;

    /**
     * @var Registry
     */
    private $registry;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->graphQlResolverCache = $this->objectManager->get(GraphQlResolverCache::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);

        // first register secure area so we have permission to delete customer in tests
        $this->registry = $this->objectManager->get(Registry::class);
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);

        parent::setUp();
    }

    protected function tearDown(): void
    {
        // reset secure area to false (was set to true in setUp so we could delete customer in tests)
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', false);

        parent::tearDown();
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_with_media_gallery_entries.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_with_media_gallery.php
     * @dataProvider invalidationMechanismProvider
     * @param callable $invalidationMechanismCallable
     */
    public function testMediaGalleryForProductVideos(callable $invalidationMechanismCallable)
    {
        // Test simple product with media
        $simpleProductWithMediaSku = 'simple_product_with_media';
        $simpleProductWithMedia = $this->productRepository->get($simpleProductWithMediaSku);
        // Query the simple product with media
        $simpleProductWithMediaQuery = $this->getQuery($simpleProductWithMediaSku);
        $simpleProductWithMediaQueryResponse =$this->graphQlQuery($simpleProductWithMediaQuery);
        $this->assertNotEmpty($simpleProductWithMediaQueryResponse['products']['items'][0]['media_gallery']);
        $this->assertMediaGalleryResolverCacheRecordExists($simpleProductWithMedia);

        // Test simple product
        $productSku = 'simple';
        $product = $this->productRepository->get($productSku);
        $this->assertMediaGalleryResolverCacheRecordDoesNotExist($product);
        $simpleProductQuerry = $this->getQuery($productSku);
        $response = $this->graphQlQuery($simpleProductQuerry);
        $this->assertNotEmpty($response['products']['items'][0]['media_gallery']);
        $this->assertMediaGalleryResolverCacheRecordExists($product);

        // Query simple product the 2nd time
        $response2 = $this->graphQlQuery($simpleProductQuerry);
        $this->assertEquals($response, $response2);

        // change product media gallory data
        $invalidationMechanismCallable($product);

        // assert that cache entry for simple product query is invalidated
        $this->assertMediaGalleryResolverCacheRecordDoesNotExist($product);

        // assert that cache entry for simple product with media query is not invalidated
        $this->assertMediaGalleryResolverCacheRecordExists($simpleProductWithMedia);

        // Query simple product the 3rd time, response is updated.
        $response3 = $this->graphQlQuery($simpleProductQuerry);
        $this->assertNotEquals($response, $response3);
   }

    public function invalidationMechanismProvider(): array
    {
        // provider is invoked before setUp() is called so need to init here
        $galleryManagement = Bootstrap::getObjectManager()->get(
            GalleryManagement::class
        );
        return [
            'update media label' => [
                function (ProductInterface $product) use ($galleryManagement) {
                    $mediaEntry = $product->getMediaGalleryEntries()[0];
                    $mediaEntry->setLabel('new_' . $mediaEntry->getLabel());
                    $galleryManagement->update($product->getSku(), $mediaEntry);
                },
            ],
        ];
    }

    /**
     * Media gallery resolver cache tags and cache key vary when query different product.
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_with_media_gallery_entries.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_with_media_gallery.php
     */
    public function testMediaGalleryResolverCacheKeyAndTags()
    {
        // Test simple product
        $simpleProductSku = 'simple';
        // Query the simple product
        $simpleProductQuery = $this->getQuery($simpleProductSku);
        $simpleProductQueryResponse = $this->graphQlQuery($simpleProductQuery);
        // Query the simple product again
        $simpleProductQueryResponse2 = $this->graphQlQuery($simpleProductQuery);
        $this->assertEquals($simpleProductQueryResponse, $simpleProductQueryResponse2);

        $simpleProduct = $this->productRepository->get($simpleProductSku);
        $simpleProductCacheKey = $this->getCacheKeyForMediaGalleryResolver($simpleProduct);
        $simplePrductCacheTags = $this->getCacheTagsUsedInMediaGalleryResolverCache($simpleProductCacheKey);
        // Verify cache tags are generated correctly for the simple product
        $this->assertEquals(
            $this->getExpectedCacheTags($simpleProduct),
            $simplePrductCacheTags
        );

        // Test simple product with media
        $simpleProductWithMediaSku = 'simple_product_with_media';
        // Query the simple product with media
        $simpleProductWithMediaQuery = $this->getQuery($simpleProductWithMediaSku);
        $simpleProductWithMediaQueryResponse =$this->graphQlQuery($simpleProductWithMediaQuery);
        $this->assertNotEquals($simpleProductQueryResponse, $simpleProductWithMediaQueryResponse);

        $simpleProductWithMedia = $this->productRepository->get($simpleProductWithMediaSku);
        $simpleProductWithMediaCacheKey = $this->getCacheKeyForMediaGalleryResolver($simpleProductWithMedia);
        $simpleProductWithMediaCacheTags = $this->getCacheTagsUsedInMediaGalleryResolverCache($simpleProductWithMediaCacheKey);
        // Verify cache tags are generated correctly for the simple product with media
        $this->assertEquals(
            $this->getExpectedCacheTags($simpleProductWithMedia),
            $simpleProductWithMediaCacheTags
        );

        // Verify different product query has different cache key
        $this->assertNotEquals($simpleProductCacheKey, $simpleProductWithMediaCacheKey);

        // Verify different product query has different cache tags
        $this->assertNotEquals($simplePrductCacheTags, $simpleProductWithMediaCacheTags);
    }

    /**
     * Assert that cache record exists.
     *
     * @param ProductInterface $product
     * @return void
     */
    private function assertMediaGalleryResolverCacheRecordExists(ProductInterface $product)
    {
        $cacheKey = $this->getCacheKeyForMediaGalleryResolver($product);
        $cacheEntry = $this->graphQlResolverCache->load($cacheKey);
        $cacheEntryDecoded = json_decode($cacheEntry, true);

        $this->assertEquals(
            $product->getMediaGalleryEntries()[0]->getLabel(),
            $cacheEntryDecoded[0]['label']
        );
    }

    /**
     * Assert that cache record does not exist.
     *
     * @param ProductInterface $product
     * @return void
     */
    private function assertMediaGalleryResolverCacheRecordDoesNotExist(ProductInterface $product)
    {
        $cacheKey = $this->getCacheKeyForMediaGalleryResolver($product);
        $this->assertFalse(
            $this->graphQlResolverCache->test($cacheKey)
        );
    }

    /**
     * Get the expected cache tags.
     *
     * @param ProductInterface $product
     * @return array
     */
    private function getExpectedCacheTags(ProductInterface $product): array
    {
        $cacheIdPrefix = $this->graphQlResolverCache->getLowLevelFrontend()->getOption('cache_id_prefix');

        return [
            $cacheIdPrefix . strtoupper(ResolverCacheIdentity::CACHE_TAG),
            $cacheIdPrefix . strtoupper(ResolverCacheIdentity::CACHE_TAG) . '_' . $product->getData('row_id'),
            $cacheIdPrefix . strtoupper(GraphQlResolverCache::CACHE_TAG),
            $cacheIdPrefix . 'MAGE',
        ];
    }

    /**
     * Get the actual cache tags used in media gallery resolver cache.
     *
     * @param string $cacheKey
     * @return array
     */
    private function getCacheTagsUsedInMediaGalleryResolverCache(string $cacheKey): array
    {
        $metadatas = $this->graphQlResolverCache->getLowLevelFrontend()->getMetadatas($cacheKey);
        return $metadatas['tags'];
    }

    /**
     * Get cache key for media gallery resolver
     *
     * @param ProductInterface $product
     * @return string
     */
    private function getCacheKeyForMediaGalleryResolver(ProductInterface $product): string
    {
        $resolverMock = $this->getMockBuilder(MediaGallery::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var ProviderInterface $cacheKeyCalculatorProvider */
        $cacheKeyCalculatorProvider = $this->objectManager->get(ProviderInterface::class);

        $cacheKeyFactor = $cacheKeyCalculatorProvider
            ->getKeyCalculatorForResolver($resolverMock)
            ->calculateCacheKey(['model' => $product]);

        $cacheKeyQueryPayloadMetadata = MediaGallery::class . '\Interceptor[]';

        $cacheKeyParts = [
            GraphQlResolverCache::CACHE_TAG,
            $cacheKeyFactor,
            sha1($cacheKeyQueryPayloadMetadata)
        ];

        // strtoupper is called in \Magento\Framework\Cache\Frontend\Adapter\Zend::_unifyId
        return strtoupper(implode('_', $cacheKeyParts));
    }

    /**
     * Get query
     *
     * @param string $productSku
     * @return string
     */
    private function getQuery(string $productSku): string
    {
        return <<<QUERY
{
  products(filter: {sku: {eq: "{$productSku}"}}) {
    items {
      small_image {
        url
      }
      media_gallery {
      	label
        url
        position
        disabled
        ... on ProductVideo {
              video_content {
                  media_type
                  video_provider
                  video_url
                  video_title
                  video_description
                  video_metadata
              }
          }
      }
    }
  }
}
QUERY;
    }
}
