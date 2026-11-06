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
 * @see Bundle
 */
class BundleTest extends TestCase
{
    /**
     * @var string
     */
    private string $staticDir;

    /**
     * Files added to the bundle of each package
     *
     * @var array<string, string[]>
     */
    private array $bundled = [];

    /**
     * Content of the package's result map file, if it has one
     *
     * @var array|null
     */
    private ?array $resultMap = null;

    /**
     * @var Bundle
     */
    private Bundle $service;

    protected function setUp(): void
    {
        $this->staticDir = sys_get_temp_dir() . '/' . uniqid('bundle-test-') . '/';
        $pubStaticDir = $this->createStub(WriteInterface::class);
        $pubStaticDir->method('isFile')->willReturnCallback(fn () => $this->resultMap !== null);
        $pubStaticDir->method('readFile')->willReturnCallback(fn () => json_encode($this->resultMap));
        $pubStaticDir->method('getAbsolutePath')->willReturnCallback(fn (string $path) => $this->staticDir . $path);
        $pubStaticDir->method('getRelativePath')
            ->willReturnCallback(fn (string $path) => substr($path, strlen($this->staticDir)));
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $pubStaticDir->method('isExist')->willReturnCallback(fn (string $path) => is_file($this->staticDir . $path));
        $filesystem = $this->createStub(Filesystem::class);
        $filesystem->method('getDirectoryWrite')->willReturn($pubStaticDir);

        $bundleFactory = $this->createStub(BundleInterfaceFactory::class);
        $bundleFactory->method('create')->willReturnCallback(function (array $arguments) {
            $package = $arguments['area'] . '/' . $arguments['theme'] . '/' . $arguments['locale'];
            $this->bundled[$package] = [];
            $bundle = $this->createStub(BundleInterface::class);
            $bundle->method('addFile')->willReturnCallback(function (string $filePath) use ($package) {
                $this->bundled[$package][] = $filePath;
                return true;
            });
            return $bundle;
        });

        // Files::getFiles() is static and lists the real directory
        $files = (new \ReflectionClass(Files::class))->newInstanceWithoutConstructor();

        $bundleConfig = $this->createStub(BundleConfig::class);
        $bundleConfig->method('getExcludedFiles')->willReturn([]);
        $bundleConfig->method('getExcludedDirectories')->willReturn([]);

        $this->service = new Bundle($filesystem, $bundleFactory, $bundleConfig, $files, new File());
    }

    protected function tearDown(): void
    {
        (new File())->rmdir($this->staticDir, true);
    }

    public function testSkipsFileWithMinifiedVersion(): void
    {
        $this->addPackage('frontend/Magento/luma/en_US', ['vimeo/player.js', 'vimeo/player.min.js', 'js/a.js']);

        $this->service->deploy('frontend', 'Magento/luma', 'en_US');

        $this->assertEqualsCanonicalizing(
            ['vimeo/player.min.js', 'js/a.js'],
            $this->bundled['frontend/Magento/luma/en_US']
        );
    }

    public function testMinifiedVersionInAnotherPackageDoesNotSkipFile(): void
    {
        $this->addPackage('frontend/Magento/luma/en_US', ['vimeo/player.min.js', 'vimeo/player.js']);
        $this->addPackage('frontend/Vendor/theme/en_US', ['vimeo/player.js']);

        $this->service->deploy('frontend', 'Magento/luma', 'en_US');
        $this->service->deploy('frontend', 'Vendor/theme', 'en_US');

        $this->assertSame(['vimeo/player.min.js'], $this->bundled['frontend/Magento/luma/en_US']);
        $this->assertSame(['vimeo/player.js'], $this->bundled['frontend/Vendor/theme/en_US']);
    }

    public function testBundlesFilesInPathOrderWhateverTheListingOrder(): void
    {
        $source = ['area' => 'frontend', 'theme' => 'Magento/luma', 'locale' => 'en_US'];
        $this->resultMap = ['js/b.js' => $source, 'Magento_Ui::js/c.js' => $source, 'js/a.js' => $source];

        $this->service->deploy('frontend', 'Magento/luma', 'en_US');

        $this->assertSame(['Magento_Ui/js/c.js', 'js/a.js', 'js/b.js'], $this->bundled['frontend/Magento/luma/en_US']);
    }

    /**
     * @param string $packagePath
     * @param string[] $files
     * @return void
     */
    private function addPackage(string $packagePath, array $files): void
    {
        $io = new File();
        foreach ($files as $file) {
            $path = $this->staticDir . $packagePath . '/' . $file;
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $io->mkdir(dirname($path));
            $io->write($path, '');
        }
    }
}
