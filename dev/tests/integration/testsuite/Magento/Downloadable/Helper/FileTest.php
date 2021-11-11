<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\MediaStorage\Model\File\Uploader;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for \Magento\Downloadable\Helper\File class
 */
class FileTest extends TestCase
{
    /**
     * @var File
     */
    private $helper;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Uploader
     */
    private $uploader;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->uploader = $this->getMockBuilder(Uploader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManager = Bootstrap::getObjectManager();
        $context = $this->objectManager->create(Context::class);
        $coreFileStorageDatabase = $this->objectManager->create(Database::class);
        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getDirectoryWrite'])
            ->getMock();
        $systemTmpDirectory = $this->getMockForAbstractClass(
            WriteInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['writeFile']
        );
        $systemTmpDirectory->method('getAbsolutePath')->willReturn(__DIR__ . DIRECTORY_SEPARATOR . 'media');

        $filesystem->method('getDirectoryWrite')->willReturn($systemTmpDirectory);
        $this->helper = $this->objectManager->create(
            File::class,
            [
                'context' => $context,
                'coreFileStorageDatabase' => $coreFileStorageDatabase,
                'filesystem' => $filesystem
            ]
        );
    }

    /**
     *
     */
    public function testUploadFromTmpSuccess()
    {
        $tmpPath = __DIR__;
        $data = ['path' => __DIR__ . DIRECTORY_SEPARATOR . 'media', 'file' => 'test_image.jpg'];
        $this->uploader->method('save')->willReturn($data);
        $result = $this->helper->uploadFromTmp($tmpPath, $this->uploader);
        $this->assertEquals('test_image.jpg', $result['file']);
        $this->assertEquals(1, count($result));
    }

    public function testUploadFromTmpFail()
    {
        $tmpPath = __DIR__;
        $this->uploader->expects($this->once())->method('save')->willReturn(false);
        $result = $this->helper->uploadFromTmp($tmpPath, $this->uploader);
        $this->assertEquals([], $result);
    }
}
