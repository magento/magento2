<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaStorage\Test\Unit\Helper\File;

use Magento\MediaStorage\Helper\File\Storage;

class StorageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /** @var \Magento\MediaStorage\Model\File\Storage\File | \PHPUnit_Framework_MockObject_MockObject  */
    protected $filesystemStorageMock;

    /** @var \Magento\MediaStorage\Helper\File\Storage\Database | \PHPUnit_Framework_MockObject_MockObject  */
    protected $coreFileStorageDbMock;

    /** @var \Magento\MediaStorage\Model\File\Storage | \PHPUnit_Framework_MockObject_MockObject  */
    protected $storageMock;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface | \PHPUnit_Framework_MockObject_MockObject  */
    protected $configMock;

    /** @var  Storage */
    protected $helper;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $className = \Magento\MediaStorage\Helper\File\Storage::class;
        $arguments = $this->objectManager->getConstructArguments($className);
        /** @var \Magento\Framework\App\Helper\Context $context */
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
            ->will($this->returnValue($currentStorage));

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
            ->will($this->returnValue($currentStorage));

        $this->assertEquals($expected, $this->helper->isInternalStorage($storage));
    }

    /**
     * @return array
     */
    public function isInternalStorageDataProvider()
    {
        return [
            'given external storage' => [5, 0, false],
            'given internal storage' => [0, 0, true],
            'not given storage' => [null, 1, false],
        ];
    }

    public function testGetStorageModel()
    {
        $storageModelMock = $this->getMockBuilder(\Magento\Framework\Model\AbstractModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storageMock->expects($this->once())
            ->method('getStorageModel')
            ->will($this->returnValue($storageModelMock));
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
            ->will($this->returnValue($storage));

        $filename = 'filename';
        $relativePath = 'relativePath';
        $this->coreFileStorageDbMock->expects($this->exactly($callNum))
            ->method('getMediaRelativePath')
            ->with($filename)
            ->will($this->returnValue($relativePath));

        $storageModelMock = $this->getMockBuilder(\Magento\Framework\Model\AbstractModel::class)
            ->disableOriginalConstructor()
            ->setMethods(['loadByFileName', '__wakeup'])
            ->getMock();
        $this->storageMock->expects($this->exactly($callNum))
            ->method('getStorageModel')
            ->will($this->returnValue($storageModelMock));
        $fileMock = $this->getMockBuilder(\Magento\MediaStorage\Model\File\Storage\Database::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', '__wakeup'])
            ->getMock();
        $storageModelMock->expects($this->exactly($callNum))
            ->method('loadByFilename')
            ->with($relativePath)
            ->will($this->returnValue($fileMock));
        $fileMock->expects($this->exactly($callNum))
            ->method('getId')
            ->will($this->returnValue($fileId));

        $this->filesystemStorageMock->expects($this->exactly($callSaveFileNum))
            ->method('saveFile')
            ->with($fileMock, true)
            ->will($this->returnValue(1));

        $this->assertEquals($expected, $this->helper->processStorageFile($filename));
    }

    /**
     * @return array
     */
    public function processStorageFileDataProvider()
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
            ->will($this->returnValue(1));
        $this->assertEquals(1, $this->helper->saveFileToFileSystem($file));
    }
}
