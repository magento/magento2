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
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MediaGallery\Plugin\Wysiwyg\Images\Storage as StoragePlugin;
use Magento\MediaGalleryApi\Api\DeleteAssetsByPathsInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Unit test for \Magento\MediaGallery\Plugin\Wysiwyg\Images\Storage
 */
class StorageTest extends TestCase
{
    private const STUB_TARGET = '/stub/test.png';
    private const STUB_RELATIVE_PATH = 'test.png';
    private const NON_STRING_PATH = 2020;
    private const INVALID_PATH = '&&';
    private const VALID_PATH = 'test-directory-path/';

    /**
     * @var DeleteAssetsByPathsInterface|MockObject
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
     * @var StoragePlugin
     */

    private $storage;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->deleteMediaAssetByPathMock = $this->getMockForAbstractClass(DeleteAssetsByPathsInterface::class);
        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->storageSubjectMock = $this->createMock(StorageSubject::class);
        $this->readInterfaceMock = $this->getMockForAbstractClass(ReadInterface::class);

        $this->storage = (new ObjectManager($this))->getObject(
            StoragePlugin::class,
            [
                'deleteMediaAssetByPath' => $this->deleteMediaAssetByPathMock,
                'filesystem' => $this->filesystemMock,
                'logger' => $this->loggerMock
            ]
        );
    }

    /**
     * @param string $path
     *
     * @dataProvider pathPathDataProvider
     */
    public function testAfterDeleteDirectory($path): void
    {
        $directoryRead = $this->getMockForAbstractClass(ReadInterface::class);
        $this->filesystemMock->expects($this->any())
            ->method('getDirectoryRead')
            ->willReturn($directoryRead);

        switch ($path) {
            case self::NON_STRING_PATH:
                $result = $this->storage->afterDeleteDirectory($this->storageSubjectMock, null, (int)$path);
                self::assertNull($result);
                break;
            case self::INVALID_PATH:
                $directoryRead->expects($this->once())
                    ->method('getRelativePath')
                    ->with($path)
                    ->willThrowException(new \Exception());
                $this->loggerMock->expects($this->once())
                    ->method('critical');
                $this->storage->afterDeleteDirectory($this->storageSubjectMock, null, $path);
                break;
            case self::VALID_PATH:
                $directoryRead->expects($this->once())
                    ->method('getRelativePath')
                    ->with($path)
                    ->willReturn($path);
                $this->deleteMediaAssetByPathMock->expects($this->once())
                    ->method('execute')
                    ->with([$path]);
                $this->storage->afterDeleteDirectory($this->storageSubjectMock, null, $path);
                break;
        }
    }

    /**
     * Data provider for path
     *
     * @return array
     */
    public function pathPathDataProvider(): array
    {
        return [
            'Non string path' => [2020],
            'Invalid path' => [self::INVALID_PATH],
            'Existent path' => [self::VALID_PATH]
        ];
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
