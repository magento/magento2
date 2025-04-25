<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Category;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Category\FileInfo;
use Magento\Catalog\Model\Category\Image;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test category image resolver
 */
class ImageTest extends TestCase
{
    /**
     * @var Store|MockObject
     */
    private $store;
    /**
     * @var Category
     */
    private $category;
    /**
     * @var Image
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $storeManager = $this->createPartialMock(StoreManager::class, ['getStore']);
        $this->store = $this->createPartialMock(Store::class, ['getBaseUrl']);
        $storeManager->method('getStore')->willReturn($this->store);
        $objectManager = new ObjectManager($this);
        $this->category = $objectManager->getObject(Category::class);
        $this->model = $objectManager->getObject(
            Image::class,
            [
                'storeManager' => $storeManager,
                'fileInfo' => $this->getFileInfo()
            ]
        );
    }

    /**
     * Test that image URL resolver works correctly with different base URL format
     *
     * @param string $baseUrl
     * @param string $imagePath
     * @param string $url
     * @dataProvider getUrlDataProvider
     */
    public function testGetUrl(string $imagePath, string $baseUrl, string $url)
    {
        $this->store->method('getBaseUrl')
            ->with(UrlInterface::URL_TYPE_MEDIA)
            ->willReturn($baseUrl);
        $this->category->setData('image_attr_code', $imagePath);
        $this->assertEquals($url, $this->model->getUrl($this->category, 'image_attr_code'));
    }

    /**
     * @return array
     */
    public static function getUrlDataProvider()
    {
        return [
            [
                'testimage',
                'http://www.example.com/',
                'http://www.example.com/catalog/category/testimage'
            ],
            [
                'testimage',
                'http://www.example.com/media/',
                'http://www.example.com/media/catalog/category/testimage'
            ],
            [
                'testimage',
                'http://www.example.com/base/path/pub/media/',
                'http://www.example.com/base/path/pub/media/catalog/category/testimage'
            ],
            [
                '/pub/media/catalog/category/testimage',
                'http://www.example.com/media/',
                'http://www.example.com/media/catalog/category/testimage'
            ],
            [
                '/pub/media/catalog/category/testimage',
                'http://www.example.com/base/path/pub/media/',
                'http://www.example.com/base/path/pub/media/catalog/category/testimage'
            ],
            [
                '/pub/media/posters/testimage',
                'http://www.example.com/media/',
                'http://www.example.com/media/posters/testimage'
            ],
            [
                '/pub/media/posters/testimage',
                'http://www.example.com/base/path/pub/media/',
                'http://www.example.com/base/path/pub/media/posters/testimage'
            ],
            [
                '',
                'http://www.example.com/',
                ''
            ]
        ];
    }

    /**
     * Get FileInfo mock
     *
     * @return MockObject
     */
    private function getFileInfo(): MockObject
    {
        $mediaDir = 'pub/media';
        $fileInfo = $this->createMock(FileInfo::class);
        $fileInfo->method('isBeginsWithMediaDirectoryPath')
            ->willReturnCallback(
                function ($path) use ($mediaDir) {
                    return strpos(ltrim($path, '/'), $mediaDir) === 0;
                }
            );
        $fileInfo->method('getRelativePathToMediaDirectory')
            ->willReturnCallback(
                function ($path) use ($mediaDir) {
                    return str_replace($mediaDir, '', $path);
                }
            );
        return $fileInfo;
    }
}
