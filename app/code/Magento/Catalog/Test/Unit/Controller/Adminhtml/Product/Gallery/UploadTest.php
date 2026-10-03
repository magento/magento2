<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product\Gallery;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\UrlInterface as BackendUrlInterface;
use Magento\Catalog\Controller\Adminhtml\Product\Gallery\Upload;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Image\Adapter\AdapterInterface;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\MediaStorage\Model\File\Uploader;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UploadTest extends TestCase
{
    private const REMOTE_MEDIA_URL = 'https://cdn.example.com/media/';
    private const REQUEST_BASE_URL = 'https://admin.example.com/';

    /**
     * @var string|null
     */
    private $responseContents;

    public function testPreviewUrlUsesUploadRequestHostWhenMediaIsStoredLocally(): void
    {
        $this->createController($this->createStub(File::class), false)->execute();

        $result = json_decode((string)$this->responseContents, true);
        $this->assertSame(
            self::REQUEST_BASE_URL . 'media/tmp/catalog/product/m/a/magento_image.jpg',
            $result['url']
        );
        $this->assertSame('/m/a/magento_image.jpg.tmp', $result['file']);
    }

    public function testPreviewUrlUsesMediaBaseWhenMediaIsStoredRemotely(): void
    {
        $this->createController($this->createStub(DriverInterface::class), false)->execute();

        $result = json_decode((string)$this->responseContents, true);
        $this->assertSame(
            self::REMOTE_MEDIA_URL . 'tmp/catalog/product/m/a/magento_image.jpg',
            $result['url']
        );
    }

    public function testPreviewUrlUsesMediaBaseWhenMediaIsStoredInDatabase(): void
    {
        $this->createController($this->createStub(File::class), true)->execute();

        $result = json_decode((string)$this->responseContents, true);
        $this->assertSame(
            self::REMOTE_MEDIA_URL . 'tmp/catalog/product/m/a/magento_image.jpg',
            $result['url']
        );
    }

    public function testPreviewUrlUsesMediaBaseWhenRequestHostIsIpv6Literal(): void
    {
        $this->createController($this->createStub(File::class), false, '[::1]:8080')->execute();

        $result = json_decode((string)$this->responseContents, true);
        $this->assertSame(
            self::REMOTE_MEDIA_URL . 'tmp/catalog/product/m/a/magento_image.jpg',
            $result['url']
        );
    }

    private function createController(
        DriverInterface $mediaDriver,
        bool $useDbStorage,
        string $httpHost = 'admin.example.com'
    ): Upload {
        $uploader = $this->createStub(Uploader::class);
        $uploader->method('save')->willReturn(
            [
                'name' => 'magento_image.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => '/tmp/phpUpload',
                'path' => '/var/www/pub/media/tmp/catalog/product',
                'file' => '/m/a/magento_image.jpg',
            ]
        );
        $objectManager = $this->createStub(ObjectManagerInterface::class);
        $objectManager->method('create')->willReturn($uploader);

        $request = $this->createStub(HttpRequest::class);
        $request->method('getDistroBaseUrl')->willReturn(self::REQUEST_BASE_URL);
        $request->method('getServer')->willReturn($httpHost);

        $backendUrl = $this->createMock(BackendUrlInterface::class);
        $backendUrl->expects($this->never())->method('getBaseUrl');

        $context = $this->createStub(Context::class);
        $context->method('getObjectManager')->willReturn($objectManager);
        $context->method('getEventManager')->willReturn($this->createStub(ManagerInterface::class));
        $context->method('getBackendUrl')->willReturn($backendUrl);
        $context->method('getRequest')->willReturn($request);

        $response = $this->createStub(Raw::class);
        $response->method('setContents')->willReturnCallback(
            function (string $contents) use (&$response) {
                $this->responseContents = $contents;
                return $response;
            }
        );
        $rawFactory = $this->createStub(RawFactory::class);
        $rawFactory->method('create')->willReturn($response);

        $adapterFactory = $this->createStub(AdapterFactory::class);
        $adapterFactory->method('create')->willReturn($this->createStub(AdapterInterface::class));

        $mediaWrite = $this->createStub(WriteInterface::class);
        $mediaWrite->method('getDriver')->willReturn($mediaDriver);
        $filesystem = $this->createStub(Filesystem::class);
        $filesystem->method('getDirectoryRead')->willReturn($this->createStub(ReadInterface::class));
        $filesystem->method('getDirectoryWrite')->willReturn($mediaWrite);
        $filesystem->method('getUri')->willReturn('media');

        $store = $this->createStub(Store::class);
        $store->method('getBaseUrl')->willReturn(self::REMOTE_MEDIA_URL);
        $storeManager = $this->createStub(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($store);

        $fileStorageDatabase = $this->createStub(Database::class);
        $fileStorageDatabase->method('checkDbUsage')->willReturn($useDbStorage);

        return new Upload(
            $context,
            $rawFactory,
            $adapterFactory,
            $filesystem,
            new Config($storeManager),
            $fileStorageDatabase
        );
    }
}
