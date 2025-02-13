<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Block\Adminhtml\Wysiwyg\Images;

use Magento\Backend\Block\Template\Context;
use Magento\Cms\Block\Adminhtml\Wysiwyg\Images\Tree;
use Magento\Cms\Helper\Wysiwyg\Images;
use Magento\Cms\Model\Wysiwyg\Images\Storage;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\DataObject;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Get tree json test.
 */
class TreeTest extends TestCase
{
    /**
     * @var Tree
     */
    private $model;

    /**
     * @var Registry|MockObject
     */
    private $coreRegistryMock;

    /**
     * @var Images|MockObject
     */
    private $cmsWysiwygImagesMock;

    /**
     * @var Storage|MockObject
     */
    private $imagesStorageMock;

    /**
     * @var Read|MockObject
     */
    private $directoryMock;

    /**
     * @var Filesystem|MockObject
     */
    private $fileSystemMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $objectManager->prepareObjectManager();

        $contextMock = $this->createMock(Context::class);
        $this->cmsWysiwygImagesMock = $this->createMock(Images::class);
        $this->coreRegistryMock = $this->createMock(Registry::class);
        $serializerMock = $this->createMock(Json::class);
        $this->imagesStorageMock = $this->createMock(Storage::class);

        $this->directoryMock = $this->getMockBuilder(Read::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRelativePath', 'isDirectory', 'getAbsolutePath', 'read'])
            ->getMock();
        $this->fileSystemMock = $this->createMock(Filesystem::class);
        $this->fileSystemMock->method('getDirectoryRead')
            ->with(DirectoryList::MEDIA)
            ->willReturn($this->directoryMock);

        $this->model = $objectManager->getObject(
            Tree::class,
            [
                'context' => $contextMock,
                'cmsWysiwygImages' => $this->cmsWysiwygImagesMock,
                'registry' => $this->coreRegistryMock,
                'serializer' => $serializerMock,
                '_filesystem' => $this->fileSystemMock
            ]
        );
    }

    /**
     * Test execute for get directories tree
     *
     * @return void
     */
    public function testGetTreeJson(): void
    {
        $collection = [];
        $this->cmsWysiwygImagesMock->method('getStorageRoot')
            ->willReturn('/storage/root/dir/');
        $this->cmsWysiwygImagesMock->method('getCurrentPath')
            ->willReturn('/storage/root/dir/pub/media/');
        $fileNames = ['fileName'];
        foreach ($fileNames as $filename) {
            /** @var DataObject|MockObject $objectMock */
            $objectMock = $this->getMockBuilder(DataObject::class)
                ->addMethods(['getFilename'])
                ->disableOriginalConstructor()
                ->getMock();
            $objectMock->method('getFilename')
                ->willReturn('/storage/root/dir/' . $filename);
            $collection[] = $objectMock;
        }
        //items for collection
        $iterator = new \ArrayIterator($collection);
        $this->imagesStorageMock->method('getDirsCollection')
            ->willReturn($iterator);
        $this->coreRegistryMock->method('registry')->willReturn($this->imagesStorageMock);
        $this->directoryMock->method('read')->willReturn($fileNames);
        $this->fileSystemMock->expects($this->once())
            ->method('getDirectoryReadByPath')
            ->willReturn($this->directoryMock);
        $this->model->getTreeJson();
    }
}
