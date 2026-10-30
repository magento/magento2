<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Deploy\Test\Unit\Service;

use Magento\Deploy\Service\DeployStaticFile;
use Magento\Framework\App\View\Asset\Publisher;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\View\Asset\Minification;
use Magento\Framework\View\Asset\PreProcessor\FileNameResolver;
use Magento\Framework\View\Asset\Repository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @see DeployStaticFile
 */
class DeployStaticFileTest extends TestCase
{
    /**
     * @var WriteInterface|MockObject
     */
    private $pubStaticDir;

    /**
     * @var DeployStaticFile
     */
    private $service;

    protected function setUp(): void
    {
        $this->pubStaticDir = $this->createMock(WriteInterface::class);
        $filesystem = $this->createStub(Filesystem::class);
        $filesystem->method('getDirectoryWrite')->willReturn($this->pubStaticDir);

        $this->service = new DeployStaticFile(
            $filesystem,
            $this->createStub(Repository::class),
            $this->createStub(Publisher::class),
            new FileNameResolver(),
            $this->createMinification()
        );
    }

    /**
     * @param string $packagePath
     * @param string $fileName
     * @param string $expectedPath
     */
    #[DataProvider('deployedPathDataProvider')]
    public function testFileExistsChecksPathTheAssetPipelinePublishes(
        string $packagePath,
        string $fileName,
        string $expectedPath
    ): void {
        $this->pubStaticDir->expects($this->once())
            ->method('isFile')
            ->with($expectedPath)
            ->willReturn(true);

        $this->assertTrue($this->service->fileExists($fileName, $packagePath));
    }

    /**
     * @return array
     */
    public static function deployedPathDataProvider(): array
    {
        return [
            'minified' => [
                'frontend/Magento/blank/fr_FR',
                'css/styles-m.css',
                'frontend/Magento/blank/fr_FR/css/styles-m.min.css',
            ],
            'excluded by a pattern that needs the full path' => [
                'frontend/Magento/blank/fr_FR',
                'hugerte/skins/ui/hugerte-5/content.css',
                'frontend/Magento/blank/fr_FR/hugerte/skins/ui/hugerte-5/content.css',
            ],
            'area read from the full path' => [
                'adminhtml/Magento/backend/fr_FR',
                'css/styles.css',
                'adminhtml/Magento/backend/fr_FR/css/styles.css',
            ],
        ];
    }

    public function testCopyFileUsesPathTheAssetPipelinePublishes(): void
    {
        $this->pubStaticDir->expects($this->once())
            ->method('copyFile')
            ->with(
                'frontend/Magento/blank/en_US/hugerte/skins/ui/hugerte-5/content.css',
                'frontend/Magento/blank/fr_FR/hugerte/skins/ui/hugerte-5/content.css'
            )
            ->willReturn(true);

        $this->assertTrue(
            $this->service->copyFile(
                'hugerte/skins/ui/hugerte-5/content.css',
                'frontend/Magento/blank/en_US',
                'frontend/Magento/blank/fr_FR'
            )
        );
    }

    /**
     * Minification enabled for the frontend only, with the default hugerte exclude
     *
     * Like the real Minification, the area is the first path segment and the exclude needs a slash before it.
     *
     * @return Minification
     */
    private function createMinification(): Minification
    {
        $minification = $this->createStub(Minification::class);
        $minification->method('addMinifiedSign')->willReturnCallback(
            static function (string $fileName): string {
                if (str_starts_with($fileName, 'adminhtml/') || str_contains($fileName, '/hugerte/')) {
                    return $fileName;
                }
                return preg_replace('/\.(\w+)$/', '.min.$1', $fileName);
            }
        );
        return $minification;
    }
}
