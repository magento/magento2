<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product\Option\Type;

use Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\DriverPool;

class FileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var ReadInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rootDirectory;

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $coreFileStorageDatabase;

    /**
     * @var Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystemMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->filesystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->rootDirectory = $this->getMockBuilder(ReadInterface::class)
            ->getMock();

        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::MEDIA, DriverPool::FILE)
            ->willReturn($this->rootDirectory);

        $this->coreFileStorageDatabase = $this->getMock(
            \Magento\MediaStorage\Helper\File\Storage\Database::class,
            ['copyFile'],
            [],
            '',
            false
        );
    }

    /**
     * @return \Magento\Catalog\Model\Product\Option\Type\File
     */
    protected function getFileObject()
    {
        return $this->objectManager->getObject(
            \Magento\Catalog\Model\Product\Option\Type\File::class,
            [
                'filesystem' => $this->filesystemMock,
                'coreFileStorageDatabase' => $this->coreFileStorageDatabase
            ]
        );
    }

    public function testCopyQuoteToOrder()
    {
        $optionMock = $this->getMockBuilder(OptionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMockForAbstractClass();

        $quotePath = '/quote/path/path/uploaded.file';
        $orderPath = '/order/path/path/uploaded.file';

        $optionMock->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue(serialize(['quote_path' => $quotePath, 'order_path' => $orderPath])));

        $this->rootDirectory->expects($this->once())
            ->method('isFile')
            ->with($this->equalTo($quotePath))
            ->will($this->returnValue(true));

        $this->rootDirectory->expects($this->any())
            ->method('isReadable')
            ->with($this->equalTo($quotePath))
            ->will($this->returnValue(true));

        $this->rootDirectory->expects($this->any())
            ->method('getAbsolutePath')
            ->will($this->returnValue('/file.path'));

        $this->coreFileStorageDatabase->expects($this->any())
            ->method('copyFile')
            ->will($this->returnValue('true'));

        $fileObject = $this->getFileObject();
        $fileObject->setData('configuration_item_option', $optionMock);

        $this->assertInstanceOf(
            \Magento\Catalog\Model\Product\Option\Type\File::class,
            $fileObject->copyQuoteToOrder()
        );
    }
}
