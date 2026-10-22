<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Verify GraphQL product image URLs materialize cache files when watermarks are configured.
 */
class ProductImageWatermarkTest extends GraphQlAbstract
{
    private const WATERMARK_FILE = 'graphql-watermark.png';

    #[
        Config('design/watermark/image_image', self::WATERMARK_FILE, ScopeInterface::SCOPE_STORE, 'default'),
        Config('design/watermark/image_position', 'center', ScopeInterface::SCOPE_STORE, 'default'),
        Config('design/watermark/image_imageOpacity', '70', ScopeInterface::SCOPE_STORE, 'default'),
        Config('design/watermark/image_size', '200x60', ScopeInterface::SCOPE_STORE, 'default'),
        Config('design/watermark/small_image_image', self::WATERMARK_FILE, ScopeInterface::SCOPE_STORE, 'default'),
        Config('design/watermark/small_image_position', 'bottom-right', ScopeInterface::SCOPE_STORE, 'default'),
        Config('design/watermark/small_image_imageOpacity', '50', ScopeInterface::SCOPE_STORE, 'default'),
        Config('design/watermark/small_image_size', '150x45', ScopeInterface::SCOPE_STORE, 'default'),
        Config('design/watermark/thumbnail_image', self::WATERMARK_FILE, ScopeInterface::SCOPE_STORE, 'default'),
        Config('design/watermark/thumbnail_position', 'bottom-right', ScopeInterface::SCOPE_STORE, 'default'),
        Config('design/watermark/thumbnail_imageOpacity', '50', ScopeInterface::SCOPE_STORE, 'default'),
        Config('design/watermark/thumbnail_size', '100x30', ScopeInterface::SCOPE_STORE, 'default'),
        DataFixture(
            ProductFixture::class,
            [
                'media_gallery_entries' => [
                    [
                        'label' => 'Image Alt Text',
                        'media_type' => 'image',
                        'position' => 1,
                        'disabled' => false,
                        'types' => ['image', 'small_image', 'thumbnail'],
                        'content' => [
                            'type' => 'image/jpeg',
                        ],
                    ],
                ],
            ],
            'product'
        )
    ]
    public function testProductImageUrlsHaveMaterializedCacheFilesWithWatermark(): void
    {
        $this->installWatermarkFile();
        $this->clearProductImageCache();

        /** @var ProductInterface $product */
        $product = DataFixtureStorageManager::getStorage()->get('product');
        $productSku = $product->getSku();
        $imageFileName = basename((string)$product->getImage());

        $query = <<<QUERY
{
  products(filter: {sku: {eq: "{$productSku}"}}) {
    items {
      sku
      image {
        url
        label
      }
      small_image {
        url
        label
      }
      thumbnail {
        url
        label
      }
    }
  }
}
QUERY;

        $response = $this->graphQlQuery($query);
        $item = $response['products']['items'][0];

        self::assertEquals($productSku, $item['sku']);

        foreach (['image', 'small_image', 'thumbnail'] as $field) {
            $url = $item[$field]['url'];
            self::assertStringContainsString(
                $imageFileName,
                $url,
                "{$field} URL should reference product image"
            );
            self::assertStringContainsString(
                '/catalog/product/cache/',
                $url,
                "{$field} URL should be a cache variant (watermark-aware hash)"
            );
            self::assertStringNotContainsString(
                'placeholder',
                $url,
                "{$field} must not fall back to placeholder when watermark is configured"
            );
            self::assertTrue(
                $this->isMediaCacheFilePresent($url),
                "{$field} cache file must exist on disk after GraphQL resolve: {$url}"
            );
            self::assertTrue(
                $this->checkImageExists($url),
                "{$field} URL must be HTTP-accessible: {$url}"
            );
        }

        // small_image / thumbnail GraphQL variants historically failed to generate under watermark
        self::assertNotSame(
            $item['image']['url'],
            $item['small_image']['url'],
            'image and small_image should use distinct cache hashes when watermark settings differ'
        );
    }

    #[
        Config('design/watermark/small_image_image', self::WATERMARK_FILE, ScopeInterface::SCOPE_STORE, 'default'),
        Config('design/watermark/small_image_position', 'center', ScopeInterface::SCOPE_STORE, 'default'),
        Config('design/watermark/small_image_imageOpacity', '60', ScopeInterface::SCOPE_STORE, 'default'),
        Config('design/watermark/small_image_size', '120x40', ScopeInterface::SCOPE_STORE, 'default'),
        DataFixture(
            ProductFixture::class,
            [
                'media_gallery_entries' => [
                    [
                        'label' => 'Image Alt Text',
                        'media_type' => 'image',
                        'position' => 1,
                        'disabled' => false,
                        'types' => ['image', 'small_image', 'thumbnail'],
                        'content' => [
                            'type' => 'image/jpeg',
                        ],
                    ],
                ],
            ],
            'product'
        )
    ]
    public function testSmallImageCacheFileIsCreatedWhenMissing(): void
    {
        $this->installWatermarkFile();
        $this->clearProductImageCache();

        /** @var ProductInterface $product */
        $product = DataFixtureStorageManager::getStorage()->get('product');
        $productSku = $product->getSku();

        $query = <<<QUERY
{
  products(filter: {sku: {eq: "{$productSku}"}}) {
    items {
      small_image {
        url
      }
    }
  }
}
QUERY;

        $response = $this->graphQlQuery($query);
        $url = $response['products']['items'][0]['small_image']['url'];

        self::assertStringContainsString('/catalog/product/cache/', $url);
        self::assertStringNotContainsString('placeholder', $url);
        self::assertTrue($this->isMediaCacheFilePresent($url), "Missing cache file for URL: {$url}");
        self::assertTrue($this->checkImageExists($url));
    }

    /**
     * Place a watermark asset under catalog/product/watermark for ParamsBuilder resolution.
     */
    private function installWatermarkFile(): void
    {
        $mediaDirectory = Bootstrap::getObjectManager()
            ->get(Filesystem::class)
            ->getDirectoryWrite(DirectoryList::MEDIA);

        $targets = [
            'catalog/product/watermark/default/' . self::WATERMARK_FILE,
            'catalog/product/watermark/' . self::WATERMARK_FILE,
            'catalog/product/watermark/stores/1/' . self::WATERMARK_FILE,
        ];

        $pngBinary = $this->createWatermarkPngBinary();
        foreach ($targets as $target) {
            $mediaDirectory->create(dirname($target));
            $mediaDirectory->writeFile($target, $pngBinary);
        }
    }

    /**
     * Remove product image cache so GraphQL resolve must materialize files.
     */
    private function clearProductImageCache(): void
    {
        $mediaDirectory = Bootstrap::getObjectManager()
            ->get(Filesystem::class)
            ->getDirectoryWrite(DirectoryList::MEDIA);
        if ($mediaDirectory->isDirectory('catalog/product/cache')) {
            $mediaDirectory->delete('catalog/product/cache');
        }
    }

    /**
     * Create a minimal PNG watermark in memory.
     */
    private function createWatermarkPngBinary(): string
    {
        $image = imagecreatetruecolor(200, 60);
        imagesavealpha($image, true);
        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefill($image, 0, 0, $transparent);
        $red = imagecolorallocatealpha($image, 200, 30, 30, 40);
        imagefilledrectangle($image, 0, 10, 199, 49, $red);

        ob_start();
        imagepng($image);
        $binary = (string)ob_get_clean();
        imagedestroy($image);

        return $binary;
    }

    /**
     * Map a media URL to the filesystem relative path and assert the file exists.
     */
    private function isMediaCacheFilePresent(string $url): bool
    {
        $path = parse_url($url, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            return false;
        }

        if (!preg_match('#/media/(.+)$#', $path, $matches)) {
            return false;
        }

        $relativePath = $matches[1];
        $mediaDirectory = Bootstrap::getObjectManager()
            ->get(Filesystem::class)
            ->getDirectoryRead(DirectoryList::MEDIA);

        return $mediaDirectory->isFile($relativePath);
    }

    /**
     * @param string $url
     * @return bool
     */
    private function checkImageExists(string $url): bool
    {
        /** @var Curl $curl */
        $curl = Bootstrap::getObjectManager()->create(Curl::class);
        $curl->setOption(CURLOPT_NOBODY, true);
        $curl->setOption(CURLOPT_SSL_VERIFYPEER, false);
        try {
            $curl->get($url);
        } catch (\Exception) {
            return false;
        }

        return $curl->getStatus() === 200;
    }
}
