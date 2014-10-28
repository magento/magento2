<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Helper\File;

class StorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /** @var \Magento\Core\Model\File\Storage\File | \PHPUnit_Framework_MockObject_MockObject  */
    protected $filesystemStorageMock;

    /** @var \Magento\Core\Helper\File\Storage\Database | \PHPUnit_Framework_MockObject_MockObject  */
    protected $coreFileStorageDbMock;

    /** @var \Magento\Core\Model\File\Storage | \PHPUnit_Framework_MockObject_MockObject  */
    protected $storageMock;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface | \PHPUnit_Framework_MockObject_MockObject  */
    protected $configMock;

    /** @var  Storage */
    protected $helper;

    public function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->filesystemStorageMock = $this->getMockBuilder('Magento\Core\Model\File\Storage\File')
            ->disableOriginalConstructor()
            ->getMock();
        $this->coreFileStorageDbMock = $this->getMockBuilder('Magento\Core\Helper\File\Storage\Database')
            ->disableOriginalConstructor()
            ->getMock();
        $this->storageMock = $this->getMockBuilder('Magento\Core\Model\File\Storage')
            ->disableOriginalConstructor()
            ->getMock();
        $this->configMock = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->helper = $this->objectManager->getObject(
            'Magento\Core\Helper\File\Storage',
            [
                'coreFileStorageDb' => $this->coreFileStorageDbMock,
                'storage' => $this->storageMock,
                'filesystemStorage' => $this->filesystemStorageMock,
                'config' => $this->configMock,
            ]
        );
    }

    public function testGetCurrentStorageCode()
    {
        $currentStorage = '10';
        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with(\Magento\Core\Model\File\Storage::XML_PATH_STORAGE_MEDIA, 'default')
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
            ->with(\Magento\Core\Model\File\Storage::XML_PATH_STORAGE_MEDIA, 'default')
            ->will($this->returnValue($currentStorage));

        $this->assertEquals($expected, $this->helper->isInternalStorage($storage));
    }

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
        $storageModelMock = $this->getMockBuilder('Magento\Framework\Model\AbstractModel')
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
            ->with(\Magento\Core\Model\File\Storage::XML_PATH_STORAGE_MEDIA, 'default')
            ->will($this->returnValue($storage));

        $filename = 'filename';
        $relativePath = 'relativePath';
        $this->coreFileStorageDbMock->expects($this->exactly($callNum))
            ->method('getMediaRelativePath')
            ->with($filename)
            ->will($this->returnValue($relativePath));

        $storageModelMock = $this->getMockBuilder('Magento\Framework\Model\AbstractModel')
            ->disableOriginalConstructor()
            ->setMethods(['loadByFileName', '__wakeup'])
            ->getMock();
        $this->storageMock->expects($this->exactly($callNum))
            ->method('getStorageModel')
            ->will($this->returnValue($storageModelMock));
        $fileMock = $this->getMockBuilder('Magento\Core\Model\File\Storage\Database')
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
