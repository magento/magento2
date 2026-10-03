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
use Magento\Framework\UrlInterface;
use Magento\MediaStorage\Model\File\Uploader;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UploadTest extends TestCase
{
    private const REMOTE_MEDIA_URL = 'https://cdn.example.com/media/';
    private const ADMIN_WEB_URL = 'https://admin.example.com/';

    /**
     * @var Filesystem|Stub
     */
    private $filesystem;

    /**
     * @var BackendUrlInterface|MockObject
     */
    private $backendUrl;

    /**
     * @var string|null
     */
    private $responseContents;

    /**
     * @var Upload
     */
    private $controller;

    protected function setUp(): void
    {
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

        $this->backendUrl = $this->createMock(BackendUrlInterface::class);

        $context = $this->createStub(Context::class);
        $context->method('getObjectManager')->willReturn($objectManager);
        $context->method('getEventManager')->willReturn($this->createStub(ManagerInterface::class));
        $context->method('getBackendUrl')->willReturn($this->backendUrl);

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

        $this->filesystem = $this->createStub(Filesystem::class);
        $this->filesystem->method('getDirectoryRead')->willReturn($this->createStub(ReadInterface::class));
        $this->filesystem->method('getUri')->willReturn('media');

        $store = $this->createStub(Store::class);
        $store->method('getBaseUrl')->willReturn(self::REMOTE_MEDIA_URL);
        $storeManager = $this->createStub(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($store);

        $this->controller = new Upload(
            $context,
            $rawFactory,
            $adapterFactory,
            $this->filesystem,
            new Config($storeManager)
        );
    }

    public function testPreviewUrlUsesAdminWebBaseWhenMediaIsStoredLocally(): void
    {
        $this->mockMediaDriver($this->createStub(File::class));
        $this->backendUrl->expects($this->once())
            ->method('getBaseUrl')
            ->with(['_type' => UrlInterface::URL_TYPE_WEB])
            ->willReturn(self::ADMIN_WEB_URL);

        $this->controller->execute();

        $result = json_decode((string)$this->responseContents, true);
        $this->assertSame(
            self::ADMIN_WEB_URL . 'media/tmp/catalog/product/m/a/magento_image.jpg',
            $result['url']
        );
        $this->assertSame('/m/a/magento_image.jpg.tmp', $result['file']);
    }

    public function testPreviewUrlUsesMediaBaseWhenMediaIsStoredRemotely(): void
    {
        $this->mockMediaDriver($this->createStub(DriverInterface::class));
        $this->backendUrl->expects($this->never())->method('getBaseUrl');

        $this->controller->execute();

        $result = json_decode((string)$this->responseContents, true);
        $this->assertSame(
            self::REMOTE_MEDIA_URL . 'tmp/catalog/product/m/a/magento_image.jpg',
            $result['url']
        );
    }

    private function mockMediaDriver(DriverInterface $driver): void
    {
        $mediaWrite = $this->createStub(WriteInterface::class);
        $mediaWrite->method('getDriver')->willReturn($driver);
        $this->filesystem->method('getDirectoryWrite')->willReturn($mediaWrite);
    }
}
