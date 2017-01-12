<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Favicon;

use \Magento\Theme\Model\Favicon\Favicon;
use Magento\Config\Model\Config\Backend\Image\Favicon as ImageFavicon;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FaviconTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Favicon
     */
    protected $object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Store\Model\Store
     */
    protected $store;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\MediaStorage\Helper\File\Storage\Database
     */
    protected $fileStorageDatabase;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $mediaDir;

    /**
     * Initialize testable object
     */
    protected function setUp()
    {
        $storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)->getMock();
        $this->store = $this->getMockBuilder(
            \Magento\Store\Model\Store::class
        )->disableOriginalConstructor()->getMock();
        $storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($this->store);
        /** @var \Magento\Store\Model\StoreManagerInterface $storeManager */
        $this->scopeManager = $this->getMockBuilder(
            \Magento\Framework\App\Config\ScopeConfigInterface::class
        )->getMock();
        $this->fileStorageDatabase = $this->getMockBuilder(\Magento\MediaStorage\Helper\File\Storage\Database::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filesystem = $this->getMockBuilder(\Magento\Framework\Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mediaDir = $this->getMockBuilder(
            \Magento\Framework\Filesystem\Directory\ReadInterface::class
        )->getMock();
        $filesystem->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::MEDIA)
            ->willReturn($this->mediaDir);
        /** @var \Magento\Framework\Filesystem $filesystem */

        $this->object = new Favicon(
            $storeManager,
            $this->scopeManager,
            $this->fileStorageDatabase,
            $filesystem
        );
    }

    /**
     * cover negative case for getFaviconFile
     */
    public function testGetFaviconFileNegative()
    {
        $this->assertFalse($this->object->getFaviconFile());
    }

    /**
     * cover positive case for getFaviconFile and checkIsFile
     */
    public function testGetFaviconFile()
    {
        $scopeConfigValue = 'path';
        $urlToMediaDir = 'http://magento.url/pub/media/';
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
        $this->mediaDir->expects($this->at(0))
            ->method('isFile')
            ->with($expectedFile)
            ->willReturn(false);
        $this->mediaDir->expects($this->at(1))
            ->method('isFile')
            ->with($expectedFile)
            ->willReturn(true);

        $results = $this->object->getFaviconFile();
        $this->assertEquals(
            $expectedUrl,
            $results
        );
        $this->assertNotFalse($results);
    }

    /**
     * cover getDefaultFavicon
     */
    public function testGetDefaultFavicon()
    {
        $this->assertEquals('Magento_Theme::favicon.ico', $this->object->getDefaultFavicon());
    }
}
