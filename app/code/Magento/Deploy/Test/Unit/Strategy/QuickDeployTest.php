<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Deploy\Test\Unit\Strategy;

use Magento\Deploy\Console\DeployStaticOptions as Options;
use Magento\Deploy\Package\Package;
use Magento\Deploy\Package\PackageFile;
use Magento\Deploy\Package\PackagePool;
use Magento\Deploy\Process\Queue;
use Magento\Deploy\Strategy\QuickDeploy;
use Magento\Framework\Translate\Js\Config as JsTranslationConfig;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Quick deployment strategy unit tests
 *
 * @see QuickDeploy
 */
class QuickDeployTest extends TestCase
{
    /**
     * @var PackagePool|MockObject
     */
    private $packagePool;

    /**
     * @var Queue|MockObject
     */
    private $queue;

    /**
     * @var JsTranslationConfig|MockObject
     */
    private $jsTranslationConfig;

    /**
     * @var array
     */
    private $options = [
        Options::NO_PARENT => false,
        Options::THEME => ['all'],
        Options::EXCLUDE_THEME => ['none'],
    ];

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->packagePool = $this->createMock(PackagePool::class);
        $this->queue = $this->createMock(Queue::class);
        $this->jsTranslationConfig = $this->createMock(JsTranslationConfig::class);
    }

    /**
     * Extra locale package must reuse deployed files of the first locale package of the same area and theme
     *
     * @return void
     */
    public function testExtraLocalePackageIsDeployedFromBaseLocalePackage(): void
    {
        $baseLocalePackage = $this->createPackage('en_US');
        $extraLocalePackage = $this->createPackage('de_DE');

        $this->jsTranslationConfig->method('dictionaryEnabled')->willReturn(true);
        $baseLocalePackage->expects($this->never())->method('setParent');
        $extraLocalePackage->expects($this->once())->method('setParent')->with($baseLocalePackage);

        $addedPackages = [];
        $this->queue->method('add')
            ->willReturnCallback(
                function (Package $package, array $dependencies) use (&$addedPackages) {
                    $addedPackages[$package->getPath()] = $dependencies;
                    return true;
                }
            );

        $this->createStrategy([$baseLocalePackage, $extraLocalePackage])->deploy($this->options);

        $this->assertSame(
            [
                'frontend/Magento/luma/en_US' => [],
                'frontend/Magento/luma/de_DE' => ['frontend/Magento/luma/en_US' => $baseLocalePackage],
            ],
            $addedPackages
        );
    }

    /**
     * Package with own locale specific files must be compiled instead of reusing another locale
     *
     * @return void
     */
    public function testPackageWithLocaleSpecificFilesIsNotDeployedFromBaseLocalePackage(): void
    {
        $ancestorPackage = $this->createMock(Package::class);
        $ancestorPackage->method('isVirtual')->willReturn(false);

        $baseLocalePackage = $this->createPackage('en_US');
        $extraLocalePackage = $this->createPackage('de_DE', true, [$ancestorPackage]);

        $this->jsTranslationConfig->method('dictionaryEnabled')->willReturn(true);
        $extraLocalePackage->expects($this->once())->method('setParent')->with($ancestorPackage);

        $this->queue->expects($this->exactly(2))
            ->method('add')
            ->with($this->anything(), []);

        $this->createStrategy([$baseLocalePackage, $extraLocalePackage])->deploy($this->options);
    }

    /**
     * Deployed JS files hold embedded translations unless dictionary strategy is used, so they can not be reused
     *
     * @return void
     */
    public function testExtraLocalePackageIsNotDeployedFromBaseLocalePackageWithoutJsDictionary(): void
    {
        $ancestorPackage = $this->createMock(Package::class);
        $ancestorPackage->method('isVirtual')->willReturn(false);

        $baseLocalePackage = $this->createPackage('en_US');
        $extraLocalePackage = $this->createPackage('de_DE', false, [$ancestorPackage]);

        $this->jsTranslationConfig->method('dictionaryEnabled')->willReturn(false);
        $extraLocalePackage->expects($this->once())->method('setParent')->with($ancestorPackage);

        $this->queue->expects($this->exactly(2))
            ->method('add')
            ->with($this->anything(), []);

        $this->createStrategy([$baseLocalePackage, $extraLocalePackage])->deploy($this->options);
    }

    /**
     * @param Package[] $packages
     * @return QuickDeploy
     */
    private function createStrategy(array $packages): QuickDeploy
    {
        $indexedPackages = [];
        foreach ($packages as $package) {
            $indexedPackages[$package->getPath()] = $package;
        }
        $this->packagePool->method('getPackagesForDeployment')->willReturn($indexedPackages);

        return new QuickDeploy($this->packagePool, $this->queue, $this->jsTranslationConfig);
    }

    /**
     * @param string $locale
     * @param bool $withLocaleSpecificFile
     * @param Package[] $parentPackages
     * @return Package|MockObject
     */
    private function createPackage(string $locale, bool $withLocaleSpecificFile = false, array $parentPackages = [])
    {
        $package = $this->createMock(Package::class);
        $package->method('isVirtual')->willReturn(false);
        $package->method('getArea')->willReturn('frontend');
        $package->method('getTheme')->willReturn('Magento/luma');
        $package->method('getLocale')->willReturn($locale);
        $package->method('getPath')->willReturn('frontend/Magento/luma/' . $locale);
        $package->method('getInheritanceLevel')->willReturn(1);
        $package->method('getParentPackages')->willReturn($parentPackages);

        $files = [];
        if ($withLocaleSpecificFile) {
            $file = $this->createMock(PackageFile::class);
            $file->method('getOrigPackage')->willReturn($package);
            $files[] = $file;
        }
        $package->method('getFiles')->willReturn($files);

        return $package;
    }
}
