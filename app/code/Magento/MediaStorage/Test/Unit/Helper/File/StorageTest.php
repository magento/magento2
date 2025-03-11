<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaStorage\Test\Unit\Helper\File;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MediaStorage\Helper\File\Storage;
use Magento\MediaStorage\Helper\File\Storage\Database as DatabaseHelper;
use Magento\MediaStorage\Model\File\Storage\File;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StorageTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /** @var File|MockObject  */
    protected $filesystemStorageMock;

    /** @var DatabaseHelper|MockObject  */
    protected $coreFileStorageDbMock;

    /** @var \Magento\MediaStorage\Model\File\Storage|MockObject  */
    protected $storageMock;

    /** @var ScopeConfigInterface|MockObject  */
    protected $configMock;

    /** @var  Storage */
    protected $helper;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $className = Storage::class;
        $arguments = $this->objectManager->getConstructArguments($className);
        /** @var Context $context */
        $context = $arguments['context'];
        $this->filesystemStorageMock = $arguments['filesystemStorage'];
        $this->coreFileStorageDbMock = $arguments['coreFileStorageDb'];
        $this->storageMock = $arguments['storage'];
        $this->configMock = $context->getScopeConfig();
        $this->helper = $this->objectManager->getObject($className, $arguments);
    }

    public function testGetCurrentStorageCode()
    {
        $currentStorage = '10';
        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with(\Magento\MediaStorage\Model\File\Storage::XML_PATH_STORAGE_MEDIA, 'default')
            ->willReturn($currentStorage);

        $this->assertEquals($currentStorage, $this->helper->getCurrentStorageCode());
        $this->assertEquals($currentStorage, $this->helper->getCurrentStorageCode());
    }

    public function testGetStorageFileModel()
    {
        $this->assertSame($this->filesystemStorageMock, $this->helper->getStorageFileModel());
    }

    /**
     * @param int $storage
     * @param int $callNum
     * @param bool $expected
     * @dataProvider isInternalStorageDataProvider
     */
    public function testIsInternalStorage($storage, $callNum, $expected)
    {
        $currentStorage = '10';
        $this->configMock->expects($this->exactly($callNum))
            ->method('getValue')
            ->with(\Magento\MediaStorage\Model\File\Storage::XML_PATH_STORAGE_MEDIA, 'default')
            ->willReturn($currentStorage);

        $this->assertEquals($expected, $this->helper->isInternalStorage($storage));
    }

    /**
     * @return array
     */
    public static function isInternalStorageDataProvider()
    {
        return [
            'given external storage' => [5, 0, false],
            'given internal storage' => [0, 0, true],
            'not given storage' => [null, 1, false],
        ];
    }

    public function testGetStorageModel()
    {
        $storageModelMock = $this->getMockBuilder(AbstractModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storageMock->expects($this->once())
            ->method('getStorageModel')
            ->willReturn($storageModelMock);
        $this->assertSame($storageModelMock, $this->helper->getStorageModel());
    }

    /**
     * @param bool|int $expected
     * @param int $storage
     * @param int $callNum
     * @param int $callSaveFileNum
     * @param int $fileId
     * @dataProvider processStorageFileDataProvider
     */
    public function testProcessStorageFile($expected, $storage, $callNum, $callSaveFileNum, $fileId = null)
    {
        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with(\Magento\MediaStorage\Model\File\Storage::XML_PATH_STORAGE_MEDIA, 'default')
            ->willReturn($storage);

        $filename = 'filename';
        $relativePath = 'relativePath';
        $this->coreFileStorageDbMock->expects($this->exactly($callNum))
            ->method('getMediaRelativePath')
            ->with($filename)
            ->willReturn($relativePath);

        $storageModelMock = $this->getMockBuilder(AbstractModel::class)
            ->disableOriginalConstructor()
            ->addMethods(['loadByFileName'])
            ->onlyMethods(['__wakeup'])
            ->getMock();
        $this->storageMock->expects($this->exactly($callNum))
            ->method('getStorageModel')
            ->willReturn($storageModelMock);
        $fileMock = $this->getMockBuilder(\Magento\MediaStorage\Model\File\Storage\Database::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId', '__wakeup'])
            ->getMock();
        $storageModelMock->expects($this->exactly($callNum))
            ->method('loadByFilename')
            ->with($relativePath)
            ->willReturn($fileMock);
        $fileMock->expects($this->exactly($callNum))
            ->method('getId')
            ->willReturn($fileId);

        $this->filesystemStorageMock->expects($this->exactly($callSaveFileNum))
            ->method('saveFile')
            ->with($fileMock, true)
            ->willReturn(1);

        $this->assertEquals($expected, $this->helper->processStorageFile($filename));
    }

    /**
     * @return array
     */
    public static function processStorageFileDataProvider()
    {
        return [
            'internal storage' => [false, 0, 0, 0],
            'external storage, no file' => [false, 5, 1, 0],
            'external storage, with file' => [1, 5, 1, 1, 1],
        ];
    }

    public function testSaveFileToFileSystem()
    {
        $file = 'file';
        $this->filesystemStorageMock->expects($this->once())
            ->method('saveFile')
            ->with($file, true)
            ->willReturn(1);
        $this->assertEquals(1, $this->helper->saveFileToFileSystem($file));
    }
}
