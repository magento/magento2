<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model\Favicon;

use Magento\Config\Model\Config\Backend\Image\Favicon as ImageFavicon;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\UrlInterface;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Model\Favicon\Favicon;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FaviconTest extends TestCase
{
    /**
     * @var Favicon
     */
    protected $object;

    /**
     * @var MockObject|Store
     */
    protected $store;

    /**
     * @var MockObject|ScopeConfigInterface
     */
    protected $scopeManager;

    /**
     * @var MockObject|Database
     */
    protected $fileStorageDatabase;

    /**
     * @var MockObject|ReadInterface
     */
    protected $mediaDir;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMock();
        $this->store = $this->getMockBuilder(
            Store::class
        )->disableOriginalConstructor()
            ->getMock();
        $storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($this->store);
        /** @var StoreManagerInterface $storeManager */
        $this->scopeManager = $this->getMockBuilder(
            ScopeConfigInterface::class
        )->getMock();
        $this->fileStorageDatabase = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mediaDir = $this->getMockBuilder(
            ReadInterface::class
        )->getMock();
        $filesystem->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::MEDIA)
            ->willReturn($this->mediaDir);
        /** @var Filesystem $filesystem */
        $this->object = new Favicon(
            $storeManager,
            $this->scopeManager,
            $this->fileStorageDatabase,
            $filesystem
        );
    }

    /**
     * cover negative case for getFaviconFile.
     *
     * @return void
     */
    public function testGetFaviconFileNegative(): void
    {
        $this->assertFalse($this->object->getFaviconFile());
    }

    /**
     * cover positive case for getFaviconFile and checkIsFile.
     *
     * @return void
     */
    public function testGetFaviconFile(): void
    {
        $scopeConfigValue = 'path';
        $urlToMediaDir = 'http://magento.url/media/';
        $expectedFile = ImageFavicon::UPLOAD_DIR . '/' . $scopeConfigValue;
        $expectedUrl = $urlToMediaDir . $expectedFile;

        $this->scopeManager->expects($this->once())
            ->method('getValue')
            ->with('design/head/shortcut_icon', ScopeInterface::SCOPE_STORE)
            ->willReturn($scopeConfigValue);
        $this->store->expects($this->once())
            ->method('getBaseUrl')
            ->with(UrlInterface::URL_TYPE_MEDIA)
            ->willReturn($urlToMediaDir);
        $this->fileStorageDatabase->expects($this->once())
            ->method('checkDbUsage')
            ->willReturn(true);
        $this->fileStorageDatabase->expects($this->once())
            ->method('saveFileToFilesystem')
            ->willReturn(true);
        $this->mediaDir
            ->method('isFile')
            ->withConsecutive([$expectedFile], [$expectedFile])
            ->willReturnOnConsecutiveCalls(false, true);

        $results = $this->object->getFaviconFile();
        $this->assertEquals(
            $expectedUrl,
            $results
        );
        $this->assertNotFalse($results);
    }

    /**
     * cover getDefaultFavicon.
     *
     * @return void
     */
    public function testGetDefaultFavicon(): void
    {
        $this->assertEquals('Magento_Theme::favicon.ico', $this->object->getDefaultFavicon());
    }
}
