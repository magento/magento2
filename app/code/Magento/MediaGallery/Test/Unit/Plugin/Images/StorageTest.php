<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Test\Unit\Plugin\Images;

use Magento\Cms\Model\Wysiwyg\Images\Storage as StorageSubject;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MediaGallery\Model\Asset\Command\DeleteByDirectoryPath;
use Magento\MediaGallery\Plugin\Wysiwyg\Images\Storage as StoragePlugin;
use Magento\MediaGalleryApi\Model\Asset\Command\DeleteByDirectoryPathInterface;
use Magento\MediaGalleryApi\Model\Asset\Command\DeleteByPathInterface;
use Magento\MediaGalleryApi\Model\Asset\Command\GetByPathInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test the DeleteByDirectoryPath command model
 */
class StorageTest extends TestCase
{
    private const NON_STRING_PATH = 2020;
    private const NON_EXISTENT_PATH = 'non_existent';
    private const INVALID_PATH = '&&';
    private const VALID_PATH = 'test-directory-path/';

    /**
     * @var GetByPathInterface|MockObject
     */
    private $getMediaAssetByPath;

    /**
     * @var DeleteByPathInterface|MockObject
     */
    private $deleteMediaAssetByPath;

    /**
     * @var DeleteByDirectoryPathInterface|MockObject
     */
    private $deleteMediaAssetByDirectoryPath;

    /**
     * @var Filesystem|MockObject
     */
    private $filesystem;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var StoragePlugin
     */
    private $storagePlugin;

    /**
     * Initialize basic test class mocks
     */
    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->getMediaAssetByPath = $this->createMock(GetByPathInterface::class);
        $this->deleteMediaAssetByPath = $this->createMock(DeleteByPathInterface::class);
        $this->deleteMediaAssetByDirectoryPath = $this->createMock(DeleteByDirectoryPath::class);
        $this->filesystem = $this->createMock(Filesystem::class);

        $this->storagePlugin = (new ObjectManager($this))->getObject(
            StoragePlugin::class,
            [
                'getMediaAssetByPath' => $this->getMediaAssetByPath,
                'deleteMediaAssetByPath' =>  $this->deleteMediaAssetByPath,
                'deleteByDirectoryPath' => $this->deleteMediaAssetByDirectoryPath,
                'filesystem' => $this->filesystem,
                'logger' => $this->logger
            ]
        );
    }

    /**
     * @param string $path
     *
     * @dataProvider pathPathDataProvider
     */
    public function testAfterDeleteDirectory(string $path): void
    {
        /** @var StorageSubject|MockObject $storageSubject */
        $storageSubject = $this->getMockBuilder(StorageSubject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $directoryRead = $this->createMock(ReadInterface::class);
        $this->filesystem->expects($this->any())
            ->method('getDirectoryRead')
            ->willReturn($directoryRead);

        switch ($path) {
            case self::NON_STRING_PATH:
                $result = $this->storagePlugin->afterDeleteDirectory($storageSubject, null, (int)$path);
                self::assertNull($result);
                break;
            case self::NON_EXISTENT_PATH:
                $directoryRead->expects($this->once())
                    ->method('getRelativePath')
                    ->with($path)
                    ->willReturn($path);
                $directoryRead->expects($this->once())
                    ->method('isExist')
                    ->with($path)
                    ->willReturn(false);
                self::expectException('Magento\Framework\Exception\CouldNotDeleteException');
                $this->storagePlugin->afterDeleteDirectory($storageSubject, null, $path);
                break;
            case self::INVALID_PATH:
                $exception = new ValidatorException(__('Path cannot be used with directory'));
                $directoryRead->expects($this->once())
                    ->method('getRelativePath')
                    ->with($path)
                    ->willThrowException($exception);
                $this->logger->expects($this->once())
                    ->method('critical')
                    ->with($exception);
                $this->storagePlugin->afterDeleteDirectory($storageSubject, null, $path);
                break;
            case self::VALID_PATH:
                $directoryRead->expects($this->once())
                    ->method('getRelativePath')
                    ->with($path)
                    ->willReturn($path);
                $directoryRead->expects($this->once())
                    ->method('isExist')
                    ->with($path)
                    ->willReturn(true);
                $this->deleteMediaAssetByDirectoryPath->expects($this->once())
                    ->method('execute')
                    ->with($path);
                $this->storagePlugin->afterDeleteDirectory($storageSubject, null, $path);
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
            'Non-existent path' => [self::NON_EXISTENT_PATH],
            'Invalid path' => [self::INVALID_PATH],
            'Existent path' => [self::VALID_PATH]
        ];
    }
}
