<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Deploy\Test\Unit\Service;

use Magento\Deploy\Config\BundleConfig;
use Magento\Deploy\Package\BundleInterface;
use Magento\Deploy\Package\BundleInterfaceFactory;
use Magento\Deploy\Service\Bundle;
use Magento\Framework\App\Utility\Files;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Io\File;
use PHPUnit\Framework\TestCase;

/**
 * Bundle composition must not depend on the order in which deployed files are discovered.
 */
class BundleTest extends TestCase
{
    /**
     * Files are packed into numbered bundles in iteration order, and Files::getFiles() globs with
     * GLOB_NOSORT, so the order files are discovered in must not decide the result.
     *
     * @return void
     */
    public function testFilesAreBundledInPathOrderRegardlessOfDiscoveryOrder(): void
    {
        $expected = ['alpha.js', 'middle.js', 'zebra.js'];

        $this->assertSame($expected, $this->bundledPaths(['zebra.js', 'alpha.js', 'middle.js']));
        $this->assertSame($expected, $this->bundledPaths(['middle.js', 'zebra.js', 'alpha.js']));
    }

    /**
     * Of a minified/unminified pair only the minified file belongs in the bundle. hasMinVersion()
     * excludes the unminified twin only once it has seen the ".min." file, so the ordering has to
     * put that one first - sorting purely by path would bundle both.
     *
     * @return void
     */
    public function testOnlyTheMinifiedTwinIsBundled(): void
    {
        $this->assertSame(
            ['widget.min.js'],
            $this->bundledPaths(['widget.js', 'widget.min.js']),
            'the unminified twin must not be bundled alongside the minified one'
        );
        $this->assertSame(
            ['widget.min.js'],
            $this->bundledPaths(['widget.min.js', 'widget.js']),
            'and that must not depend on which of the pair was discovered first'
        );
    }

    /**
     * A minified file with no unminified twin is still bundled.
     *
     * @return void
     */
    public function testUnpairedFilesAreUnaffected(): void
    {
        $this->assertSame(
            ['a.min.js', 'b.js'],
            $this->bundledPaths(['b.js', 'a.min.js'])
        );
    }

    /**
     * Run deploy() over a discovery order and return the paths handed to the bundle.
     *
     * Driven through the map-file branch: the filesystem-scan branch calls Files::getFiles(),
     * which is static and cannot be stubbed.
     *
     * @param string[] $discoveryOrder
     * @return string[]
     */
    private function bundledPaths(array $discoveryOrder): array
    {
        $map = [];
        foreach ($discoveryOrder as $path) {
            $map[$path] = ['area' => 'frontend', 'theme' => 'Test/theme', 'locale' => 'en_US'];
        }

        $pubStaticDir = $this->createMock(WriteInterface::class);
        $pubStaticDir->method('isFile')->willReturn(true);
        $pubStaticDir->method('readFile')->willReturn(json_encode($map));
        // Mirrors production: hasMinVersion() probes a package-relative path against a directory
        // rooted at pub/static, so this never resolves.
        $pubStaticDir->method('isExist')->willReturn(false);

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->method('getDirectoryWrite')->willReturn($pubStaticDir);

        $collected = [];
        $bundle = $this->createMock(BundleInterface::class);
        $bundle->method('addFile')->willReturnCallback(
            function ($filePath) use (&$collected) {
                $collected[] = $filePath;
                return true;
            }
        );
        $bundleFactory = $this->createMock(BundleInterfaceFactory::class);
        $bundleFactory->method('create')->willReturn($bundle);

        $bundleConfig = $this->createMock(BundleConfig::class);
        $bundleConfig->method('getExcludedFiles')->willReturn([]);
        $bundleConfig->method('getExcludedDirectories')->willReturn([]);

        $file = $this->createMock(File::class);
        $file->method('getPathInfo')->willReturnCallback(static fn($path) => pathinfo($path));

        $service = new Bundle(
            $filesystem,
            $bundleFactory,
            $bundleConfig,
            $this->createMock(Files::class),
            $file
        );
        $service->deploy('frontend', 'Test/theme', 'en_US');

        return $collected;
    }
}
