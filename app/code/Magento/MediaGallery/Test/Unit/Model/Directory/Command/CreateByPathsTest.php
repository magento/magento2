<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Test\Unit\Model\Directory\Command;

use Magento\Cms\Helper\Wysiwyg\Images;
use Magento\Cms\Model\Wysiwyg\Images\Storage;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\MediaGallery\Model\Directory\Command\CreateByPaths;
use Magento\MediaGalleryApi\Api\IsPathExcludedInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Verify directory creation failures distinguish restrictions from storage errors.
 */
class CreateByPathsTest extends TestCase
{
    /**
     * @var Storage|MockObject
     */
    private $storage;

    /**
     * @var IsPathExcludedInterface|MockObject
     */
    private $isPathExcluded;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $config;

    /**
     * @var CreateByPaths
     */
    private CreateByPaths $command;

    /**
     * @inheritdoc
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->storage = $this->createMock(Storage::class);
        $this->isPathExcluded = $this->createMock(IsPathExcludedInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->config = $this->createMock(ScopeConfigInterface::class);
        $this->command = new CreateByPaths($this->logger, $this->storage, $this->isPathExcluded, $this->config);
    }

    /**
     * Excluded paths never reach storage and report only valid configured folder names.
     *
     * @param mixed $folders
     * @param string $explanation
     * @return void
     */
    #[DataProvider('allowedFoldersProvider')]
    public function testExcludedPaths(mixed $folders, string $explanation): void
    {
        $this->isPathExcluded->expects($this->exactly(2))->method('execute')->willReturnMap([
            ['first', true],
            ['second', true]
        ]);
        $this->config->expects($this->once())->method('getValue')->with(
            'system/media_storage_configuration/allowed_resources/media_gallery_image_folders',
            'default'
        )->willReturn($folders);
        $this->storage->expects($this->never())->method('createDirectory');
        $this->logger->expects($this->never())->method('critical');

        $this->expectException(CouldNotSaveException::class);
        $this->expectExceptionMessage('Could not create directories: first ,second. ' . $explanation);
        $this->command->execute(['first', 'second']);
    }

    /**
     * Supply standard, custom and malformed allowed-folder configuration.
     *
     * @return array
     */
    public static function allowedFoldersProvider(): array
    {
        $fallback = 'These paths are outside the allowed Media Gallery folders.';
        return [
            'default folders' => [
                ['wysiwyg_image_folder' => 'wysiwyg', 'catalog_category_image_folder' => 'catalog/category'],
                'You can create folders only inside these folders: wysiwyg, catalog/category.'
            ],
            'custom folders' => [
                ['custom' => 'brand/images', 'other' => 'campaign'],
                'You can create folders only inside these folders: brand/images, campaign.'
            ],
            'invalid entries' => [
                [null, [], false, 12, '', ' ', 'custom/images'],
                'You can create folders only inside these folders: custom/images.'
            ],
            'trimmed unique folders' => [
                [' brand/images ', 'brand/images', ' campaign ', 'campaign'],
                'You can create folders only inside these folders: brand/images, campaign.'
            ],
            'empty array' => [[], $fallback],
            'null' => [null, $fallback],
            'string' => ['wysiwyg', $fallback],
            'boolean' => [false, $fallback]
        ];
    }

    /**
     * Storage failures retain the generic message and are logged without reading folder configuration.
     *
     * @return void
     */
    public function testStorageFailure(): void
    {
        $failure = new \Exception('Permission denied');
        $this->isPathExcluded->expects($this->once())->method('execute')->with('wysiwyg/test')->willReturn(false);
        $this->configureStorageRoot();
        $this->storage->expects($this->once())->method('createDirectory')->with('test', '/media/wysiwyg/')
            ->willThrowException($failure);
        $this->logger->expects($this->once())->method('critical')->with($failure);
        $this->config->expects($this->never())->method('getValue');

        try {
            $this->command->execute(['wysiwyg/test']);
            $this->fail('Expected storage failure.');
        } catch (CouldNotSaveException $exception) {
            $this->assertSame('Could not create directories: wysiwyg/test', $exception->getMessage());
            $this->assertSame('Could not create directories: %paths', $exception->getRawMessage());
        }
    }

    /**
     * Mixed batches report each failure category and still attempt all allowed paths.
     *
     * @return void
     */
    public function testMixedFailures(): void
    {
        $failure = new \Exception('Permission denied');
        $this->isPathExcluded->expects($this->exactly(3))->method('execute')->willReturnMap([
            ['root', true],
            ['wysiwyg/failed', false],
            ['wysiwyg/created', false]
        ]);
        $this->configureStorageRoot();
        $this->storage->expects($this->exactly(2))->method('createDirectory')
            ->willReturnCallback(static function (string $name, string $parent) use ($failure): array {
                self::assertSame('/media/wysiwyg/', $parent);
                if ($name === 'failed') {
                    throw $failure;
                }
                self::assertSame('created', $name);
                return [];
            });
        $this->logger->expects($this->once())->method('critical')->with($failure);
        $this->config->expects($this->once())->method('getValue')->willReturn(['wysiwyg']);

        try {
            $this->command->execute(['root', 'wysiwyg/failed', 'wysiwyg/created']);
            $this->fail('Expected both failure categories.');
        } catch (CouldNotSaveException $exception) {
            $this->assertSame(
                'Could not create directories: root. You can create folders only inside these folders: wysiwyg. '
                . 'Could not create directories: wysiwyg/failed',
                $exception->getMessage()
            );
            $this->assertNotSame($exception->getMessage(), $exception->getRawMessage());
        }
    }

    /**
     * Empty batches and successful creation need no restriction guidance.
     *
     * @return void
     */
    public function testSuccessfulCreation(): void
    {
        $this->isPathExcluded->expects($this->once())->method('execute')->with('wysiwyg/test')->willReturn(false);
        $this->configureStorageRoot();
        $this->storage->expects($this->once())->method('createDirectory')->with('test', '/media/wysiwyg/');
        $this->logger->expects($this->never())->method('critical');
        $this->config->expects($this->never())->method('getValue');

        $this->command->execute([]);
        $this->command->execute(['wysiwyg/test']);
    }

    /**
     * Configure storage without accessing the filesystem.
     *
     * @return void
     */
    private function configureStorageRoot(): void
    {
        $images = $this->createMock(Images::class);
        $images->method('getStorageRoot')->willReturn('/media/');
        $this->storage->method('getCmsWysiwygImages')->willReturn($images);
    }
}
