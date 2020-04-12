<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGallery\Test\Unit\Plugin\Wysiwyg\Images;

use Magento\Cms\Model\Wysiwyg\Images\Storage as StorageSubject;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\MediaGallery\Plugin\Wysiwyg\Images\Storage;
use Magento\MediaGalleryApi\Model\Asset\Command\DeleteByPathInterface;
use Magento\MediaGalleryApi\Model\Asset\Command\GetByPathInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Unit test for \Magento\MediaGallery\Plugin\Wysiwyg\Images\Storage
 */
class StorageTest extends TestCase
{
    const STUB_TARGET = '/stub/test.png';
    const STUB_RELATIVE_PATH = 'test.png';

    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var GetByPathInterface|MockObject
     */
    private $getMediaAssetByPathMock;

    /**
     * @var DeleteByPathInterface|MockObject
     */
    private $deleteMediaAssetByPathMock;

    /**
     * @var Filesystem|MockObject
     */
    private $filesystemMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var StorageSubject|MockObject
     */
    private $storageSubjectMock;

    /**
     * @var ReadInterface|MockObject
     */
    private $readInterfaceMock;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->storageSubjectMock = $this->createMock(StorageSubject::class);
        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->getMediaAssetByPathMock = $this->createMock(GetByPathInterface::class);
        $this->deleteMediaAssetByPathMock = $this->getMockBuilder(DeleteByPathInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['execute'])
            ->getMockForAbstractClass();
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['critical'])
            ->getMockForAbstractClass();
        $this->readInterfaceMock = $this->getMockBuilder(ReadInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRelativePath'])
            ->getMockForAbstractClass();

        $this->storage = (new ObjectManagerHelper($this))->getObject(
            Storage::class,
            [
                'getMediaAssetByPath' => $this->getMediaAssetByPathMock,
                'deleteMediaAssetByPath' => $this->deleteMediaAssetByPathMock,
                'filesystem' => $this->filesystemMock,
                'logger' => $this->loggerMock
            ]
        );
    }

    /**
     * Test case when an exception is thrown during the method execution.
     */
    public function testAfterDeleteFileExpectsDeleteMediaAssetExecuted()
    {
        $this->setupMocksToReturnCorrectRelativePath();
        $this->deleteMediaAssetByPathMock->expects($this->once())->method('execute');
        $this->loggerMock->expects($this->never())->method('critical');

        $this->executeOriginalMethodWithCorrectTarget();
    }

    /**
     * Test case when an exception is thrown during the method execution.
     */
    public function testAfterDeleteFileWithException()
    {
        $this->setupMocksToReturnCorrectRelativePath();
        $this->deleteMediaAssetByPathMock->expects($this->once())
            ->method('execute')
            ->willThrowException(new \Exception());
        $this->loggerMock->expects($this->once())->method('critical');

        $this->executeOriginalMethodWithCorrectTarget();
    }

    /**
     * Test case when the target is not a string.
     */
    public function testAfterDeleteFileWhenTargetIsNotString()
    {
        $target = [];
        $this->filesystemMock->expects($this->never())->method('getDirectoryRead');
        $this->deleteMediaAssetByPathMock->expects($this->never())->method('execute');
        $this->assertSame(
            $this->storageSubjectMock,
            $this->storage->afterDeleteFile($this->storageSubjectMock, $this->storageSubjectMock, $target)
        );
    }

    /**
     * Test case when there is no Relative Path which is need to be deleted.
     */
    public function testAfterDeleteFileWhenRelativePathIsEmpty()
    {
        $this->readInterfaceMock->expects($this->once())
            ->method('getRelativePath')
            ->willReturn('');
        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryRead')
            ->willReturn($this->readInterfaceMock);

        $this->deleteMediaAssetByPathMock->expects($this->never())->method('execute');
        $this->executeOriginalMethodWithCorrectTarget();
    }

    /**
     * Call the tested method
     */
    private function executeOriginalMethodWithCorrectTarget()
    {
        $this->assertSame(
            $this->storageSubjectMock,
            $this->storage->afterDeleteFile($this->storageSubjectMock, $this->storageSubjectMock, self::STUB_TARGET)
        );
    }

    /**
     * Set mocks in order to return the relative path
     */
    private function setupMocksToReturnCorrectRelativePath()
    {
        $this->readInterfaceMock->expects($this->once())
            ->method('getRelativePath')
            ->willReturn(self::STUB_RELATIVE_PATH);
        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryRead')
            ->willReturn($this->readInterfaceMock);
    }
}
