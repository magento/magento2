<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Deploy\Test\Unit\Service;

use Magento\Deploy\Console\DeployStaticOptions as Options;
use Magento\Deploy\Package\Package;
use Magento\Deploy\Package\PackageFile;
use Magento\Deploy\Service\DeployPackage;
use Magento\Deploy\Service\DeployStaticFile;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Locale\ResolverInterface as LocaleResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Deploy package service unit tests
 *
 * @see DeployPackage
 */
class DeployPackageTest extends TestCase
{
    private const FILE_ID = 'Magento_Theme::css/styles-m.css';

    /**
     * @var DeployStaticFile|MockObject
     */
    private $deployStaticFile;

    /**
     * @var DeployPackage
     */
    private $service;

    /**
     * @var Package|MockObject
     */
    private $origPackage;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->deployStaticFile = $this->createMock(DeployStaticFile::class);
        $this->origPackage = $this->createMock(Package::class);

        $this->service = new DeployPackage(
            $this->createMock(AppState::class),
            $this->createMock(LocaleResolver::class),
            $this->deployStaticFile,
            $this->createMock(LoggerInterface::class)
        );
    }

    /**
     * File inherited by both packages from the same ancestor is copied from the parent package
     *
     * @return void
     */
    public function testFileIsCopiedFromPackageOfAnotherLocale(): void
    {
        $parentPackage = $this->createPackage('en_US');
        $parentPackage->method('getFile')->with(self::FILE_ID)->willReturn($this->createInheritedFile());

        $package = $this->createPackage('de_DE', $parentPackage);
        $package->method('getFiles')->willReturn([$this->createInheritedFile()]);

        $this->deployStaticFile->method('readFile')
            ->with(self::FILE_ID, 'frontend/Magento/luma/en_US')
            ->willReturn('content');
        $this->deployStaticFile->expects($this->once())
            ->method('copyFile')
            ->with(self::FILE_ID, 'frontend/Magento/luma/en_US', 'frontend/Magento/luma/de_DE');
        $this->deployStaticFile->expects($this->never())->method('deployFile');

        $this->service->deployEmulated($package, $this->getOptions(), true);
    }

    /**
     * File overridden in the parent package holds parent specific content and must not be reused
     *
     * @return void
     */
    public function testFileOverriddenInParentPackageIsDeployed(): void
    {
        $overriddenFile = $this->createInheritedFile('en_US');

        $parentPackage = $this->createPackage('en_US');
        $parentPackage->method('getFile')->with(self::FILE_ID)->willReturn($overriddenFile);

        $package = $this->createPackage('de_DE', $parentPackage);
        $package->method('getFiles')->willReturn([$this->createInheritedFile()]);

        $this->deployStaticFile->expects($this->never())->method('copyFile');
        $this->deployStaticFile->expects($this->once())
            ->method('deployFile')
            ->with(
                'css/styles-m.css',
                [
                    'area' => 'frontend',
                    'theme' => 'Magento/luma',
                    'locale' => 'de_DE',
                    'module' => 'Magento_Theme',
                ]
            );

        $this->service->deployEmulated($package, $this->getOptions(), true);
    }

    /**
     * File missing in the parent package must be deployed from source
     *
     * @return void
     */
    public function testFileMissingInParentPackageIsDeployed(): void
    {
        $parentPackage = $this->createPackage('en_US');
        $parentPackage->method('getFile')->with(self::FILE_ID)->willReturn(false);

        $package = $this->createPackage('de_DE', $parentPackage);
        $package->method('getFiles')->willReturn([$this->createInheritedFile()]);

        $this->deployStaticFile->expects($this->never())->method('copyFile');
        $this->deployStaticFile->expects($this->once())->method('deployFile');

        $this->service->deployEmulated($package, $this->getOptions(), true);
    }

    /**
     * @return array
     */
    private function getOptions(): array
    {
        return [Options::NO_CSS => false];
    }

    /**
     * @param string $locale
     * @param Package|null $parentPackage
     * @return Package|MockObject
     */
    private function createPackage(string $locale, ?Package $parentPackage = null)
    {
        $package = $this->createMock(Package::class);
        $package->method('getArea')->willReturn('frontend');
        $package->method('getTheme')->willReturn('Magento/luma');
        $package->method('getLocale')->willReturn($locale);
        $package->method('getPath')->willReturn('frontend/Magento/luma/' . $locale);
        $package->method('getParent')->willReturn($parentPackage);
        $package->method('getPostProcessors')->willReturn([]);

        return $package;
    }

    /**
     * @param string $locale
     * @return PackageFile|MockObject
     */
    private function createInheritedFile(string $locale = 'default')
    {
        $file = $this->createMock(PackageFile::class);
        $file->method('getFileId')->willReturn(self::FILE_ID);
        $file->method('getDeployedFileId')->willReturn(self::FILE_ID);
        $file->method('getFileName')->willReturn('css/styles-m.css');
        $file->method('getModule')->willReturn('Magento_Theme');
        $file->method('getArea')->willReturn('frontend');
        $file->method('getTheme')->willReturn('Magento/luma');
        $file->method('getLocale')->willReturn($locale);
        $file->method('getOrigPackage')->willReturn($this->origPackage);

        return $file;
    }
}
