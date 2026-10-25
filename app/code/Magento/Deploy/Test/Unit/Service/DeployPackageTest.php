<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Deploy\Test\Unit\Service;

use Magento\Deploy\Package\Package;
use Magento\Deploy\Package\PackageFile;
use Magento\Deploy\Package\PackagePool;
use Magento\Deploy\Service\DeployPackage;
use Magento\Deploy\Service\DeployStaticFile;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Locale\ResolverInterface as LocaleResolver;
use Magento\Framework\Translate\Js\Config as JsTranslationConfig;
use Magento\Framework\View\Asset\PreProcessor\FileNameResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @see DeployPackage
 */
class DeployPackageTest extends TestCase
{
    private const LUMA = 'Magento/luma';

    /**
     * @var DeployStaticFile|MockObject
     */
    private $deployStaticFile;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * Theme files of Magento/luma, collected without locale
     *
     * @var Package
     */
    private $themePackage;

    /**
     * @var Package
     */
    private $parentPackage;

    /**
     * @var Package
     */
    private $package;

    protected function setUp(): void
    {
        $this->deployStaticFile = $this->createMock(DeployStaticFile::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        // DeployPackage swallows exceptions from processing a file and logs them instead
        $this->logger->expects($this->never())->method('critical');

        $this->themePackage = $this->createPackage(self::LUMA, Package::BASE_LOCALE, true);
        $this->parentPackage = $this->createPackage(self::LUMA, 'en_US');
        $this->package = $this->createPackage(self::LUMA, 'de_DE');
        $this->package->setParent($this->parentPackage);
        $this->package->setParam('copy_from_parent', true);
    }

    public function testCopiesInheritedFileWithSameSourceFromParent(): void
    {
        $this->inherit($this->createFile($this->themePackage, 'images/logo.svg'));
        $this->deployStaticFile->method('fileExists')->willReturn(true);

        $this->expectCopy('images/logo.svg');
        $this->deploy();
    }

    public function testDeploysFileWhenParentResolvesDifferentSource(): void
    {
        $this->inherit($this->createFile($this->themePackage, 'images/logo.svg'), $this->package);
        $this->createFile($this->parentPackage, 'images/logo.svg', '/Magento/luma/web/i18n/en_US/images/logo.svg');
        $this->deployStaticFile->method('fileExists')->willReturn(true);

        $this->expectDeploy('images/logo.svg');
        $this->deploy();
    }

    public function testDeploysFileOwnedByPackage(): void
    {
        $this->inherit($this->createFile($this->themePackage, 'images/logo.svg'), $this->parentPackage);
        $this->createFile($this->package, 'images/logo.svg', '/Magento/luma/web/images/logo.svg');
        $this->deployStaticFile->method('fileExists')->willReturn(true);

        $this->expectDeploy('images/logo.svg');
        $this->deploy();
    }

    public function testDeploysFileMissingFromParentOutput(): void
    {
        $this->inherit($this->createFile($this->themePackage, 'images/logo.svg'));
        $this->deployStaticFile->method('fileExists')->willReturn(false);

        $this->expectDeploy('images/logo.svg');
        $this->deploy();
    }

    public function testDeploysFileWithoutParentPackage(): void
    {
        $package = $this->createPackage(self::LUMA, 'en_US');
        $this->inherit($this->createFile($this->themePackage, 'images/logo.svg'), $package);
        $this->deployStaticFile->method('fileExists')->willReturn(true);

        $this->deployStaticFile->expects($this->never())->method('copyFile');
        $this->deployStaticFile->expects($this->once())
            ->method('deployFile')
            ->with('images/logo.svg', $this->deployParams('en_US'));
        $this->createService()->deployEmulated($package, [], true);
    }

    public function testDeploysFileWhenStrategyDidNotEnableCopying(): void
    {
        $package = $this->createPackage(self::LUMA, 'de_DE');
        $package->setParent($this->parentPackage);
        $this->inherit($this->createFile($this->themePackage, 'images/logo.svg'), $this->parentPackage, $package);
        $this->deployStaticFile->method('fileExists')->willReturn(true);

        $this->expectDeploy('images/logo.svg');
        $this->createService()->deployEmulated($package, [], true);
    }

    public function testWritesFileWithContent(): void
    {
        $file = $this->createFile($this->package, 'requirejs-config.js');
        $file->setContent('require.config({});');

        $this->deployStaticFile->expects($this->once())
            ->method('writeFile')
            ->with('requirejs-config.js', $this->package->getPath(), 'require.config({});');
        $this->deployStaticFile->expects($this->never())->method('copyFile');
        $this->deployStaticFile->expects($this->never())->method('deployFile');
        $this->deploy();
    }

    public function testCopiesLessRootWhenStyleSourcesMatchParent(): void
    {
        $this->inherit($this->createFile($this->themePackage, 'css/styles-m.less', null, 'css/styles-m.css'));
        $this->inherit($this->createFile($this->themePackage, 'css/source/_theme.less'));
        $this->deployStaticFile->method('fileExists')->willReturn(true);

        $this->expectCopy('css/styles-m.css');
        $this->deploy();
    }

    public function testCompilesLessRootInDeveloperModeButCopiesOtherFiles(): void
    {
        $this->inherit($this->createFile($this->themePackage, 'css/styles-m.less', null, 'css/styles-m.css'));
        $this->inherit($this->createFile($this->themePackage, 'css/source/_theme.less'));
        $this->inherit($this->createFile($this->themePackage, 'images/logo.svg'));
        $this->deployStaticFile->method('fileExists')->willReturn(true);

        $this->deployStaticFile->expects($this->once())
            ->method('copyFile')
            ->with('images/logo.svg', $this->parentPackage->getPath(), $this->package->getPath());
        $this->deployStaticFile->expects($this->once())
            ->method('deployFile')
            ->with('css/styles-m.less', $this->deployParams('de_DE'));
        $this->deploy(true, AppState::MODE_DEVELOPER);
    }

    public function testCompilesLessRootWhenPackageHasDifferentStyleSource(): void
    {
        $this->inherit($this->createFile($this->themePackage, 'css/styles-m.less', null, 'css/styles-m.css'));
        $this->inherit($this->createFile($this->themePackage, 'css/source/_theme.less'), $this->parentPackage);
        $this->createFile(
            $this->package,
            'css/source/_theme.less',
            '/Magento/luma/web/i18n/de_DE/css/source/_theme.less'
        );
        $this->deployStaticFile->method('fileExists')->willReturn(true);

        $this->expectDeploy('css/styles-m.less');
        $this->deploy();
    }

    public function testCopiesLessRootReScopedToPackageWhenStyleSourcesMatchParent(): void
    {
        $root = $this->createFile($this->themePackage, 'css/styles-m.less', null, 'css/styles-m.css');
        $this->inherit($root, $this->parentPackage);
        $this->reScope($root, $this->package);
        $this->inherit($this->createFile($this->themePackage, 'css/source/_theme.less'));
        $this->deployStaticFile->method('fileExists')->willReturn(true);

        $packageRoot = $this->package->getFile('css/styles-m.less');
        $this->assertSame('de_DE', $packageRoot->getLocale());
        $this->assertSame($this->themePackage, $packageRoot->getOrigPackage());
        $this->expectCopy('css/styles-m.css');
        $this->deploy();
    }

    public function testCompilesLessRootReScopedToPackageWhenPackageHasDifferentStyleSource(): void
    {
        $root = $this->createFile($this->themePackage, 'css/styles-m.less', null, 'css/styles-m.css');
        $this->inherit($root, $this->parentPackage);
        $this->reScope($root, $this->package);
        $this->inherit($this->createFile($this->themePackage, 'css/source/_theme.less'), $this->parentPackage);
        $this->createFile(
            $this->package,
            'css/source/_theme.less',
            '/Magento/luma/web/i18n/de_DE/css/source/_theme.less'
        );
        $this->deployStaticFile->method('fileExists')->willReturn(true);

        $this->expectDeploy('css/styles-m.less');
        $this->deploy();
    }

    /**
     * @param string $parentTheme
     * @param string $parentLocale
     * @param bool $dictionaryEnabled
     * @param bool $copy
     */
    #[DataProvider('jsTranslationDataProvider')]
    public function testCopiesJsFileOnlyWithTranslationDictionary(
        string $parentTheme,
        string $parentLocale,
        bool $dictionaryEnabled,
        bool $copy
    ): void {
        $themePackage = $this->createPackage($parentTheme, Package::BASE_LOCALE, true);
        $parentPackage = $this->createPackage($parentTheme, $parentLocale);
        $this->package->setParent($parentPackage);
        $this->inherit($this->createFile($themePackage, 'js/theme.js'), $parentPackage, $this->package);
        $this->deployStaticFile->method('fileExists')->willReturn(true);

        if ($copy) {
            $this->expectCopy('js/theme.js');
        } else {
            $this->expectDeploy('js/theme.js');
        }
        $this->deploy($dictionaryEnabled);
    }

    /**
     * @return array
     */
    public static function jsTranslationDataProvider(): array
    {
        return [
            'other locale, dictionary strategy' => [self::LUMA, 'en_US', true, true],
            'other locale, embedded or no strategy' => [self::LUMA, 'en_US', false, false],
            'other theme, dictionary strategy' => ['Magento/blank', 'de_DE', true, true],
            'other theme, embedded or no strategy' => ['Magento/blank', 'de_DE', false, false],
        ];
    }

    /**
     * Create a package, virtual packages hold the files collected for a theme without locale
     *
     * @param string $theme
     * @param string $locale
     * @param bool $isVirtual
     * @return Package
     */
    private function createPackage(string $theme, string $locale, bool $isVirtual = false): Package
    {
        return new Package(
            $this->createStub(PackagePool::class),
            $this->createStub(FileNameResolver::class),
            'frontend',
            $theme,
            $locale,
            $isVirtual
        );
    }

    /**
     * Create a file collected into the given package, as the deployment collector does
     *
     * @param Package $package
     * @param string $fileName
     * @param string|null $sourcePath
     * @param string|null $deployedFileName
     * @return PackageFile
     */
    private function createFile(
        Package $package,
        string $fileName,
        ?string $sourcePath = null,
        ?string $deployedFileName = null
    ): PackageFile {
        $file = new PackageFile(
            $fileName,
            $sourcePath ?? '/' . $package->getTheme() . '/web/' . $fileName,
            $package->getArea(),
            $package->getTheme(),
            $package->isVirtual() ? null : $package->getLocale()
        );
        $file->setDeployedFileName($deployedFileName ?? $fileName);
        $file->setPackage($package);
        return $file;
    }

    /**
     * Add the file to packages the way Package::aggregate() does
     *
     * @param PackageFile $file
     * @param Package ...$packages
     * @return void
     */
    private function inherit(PackageFile $file, Package ...$packages): void
    {
        foreach ($packages ?: [$this->parentPackage, $this->package] as $package) {
            (clone $file)->setPackage($package);
        }
    }

    /**
     * Add the file to the package with the scope of the package, the way the Less pre-processor does
     *
     * @param PackageFile $file
     * @param Package $package
     * @return void
     */
    private function reScope(PackageFile $file, Package $package): void
    {
        $file = clone $file;
        $file->setArea($package->getArea());
        $file->setTheme($package->getTheme());
        $file->setLocale($package->getLocale());
        $file->setPackage($package);
    }

    /**
     * @param string $deployedFileId
     * @return void
     */
    private function expectCopy(string $deployedFileId): void
    {
        $this->deployStaticFile->expects($this->once())
            ->method('copyFile')
            ->with($deployedFileId, $this->package->getParent()->getPath(), $this->package->getPath());
        $this->deployStaticFile->expects($this->never())->method('deployFile');
    }

    /**
     * @param string $fileName
     * @return void
     */
    private function expectDeploy(string $fileName): void
    {
        $this->deployStaticFile->expects($this->never())->method('copyFile');
        $this->deployStaticFile->expects($this->once())
            ->method('deployFile')
            ->with($fileName, $this->deployParams('de_DE'));
    }

    /**
     * @param string $locale
     * @return array
     */
    private function deployParams(string $locale): array
    {
        return ['area' => 'frontend', 'theme' => self::LUMA, 'locale' => $locale, 'module' => null];
    }

    /**
     * @param bool $dictionaryEnabled
     * @param string $mode
     * @return void
     */
    private function deploy(bool $dictionaryEnabled = true, string $mode = AppState::MODE_PRODUCTION): void
    {
        $this->assertTrue(
            $this->createService($dictionaryEnabled, $mode)->deployEmulated($this->package, [], true)
        );
    }

    /**
     * @param bool $dictionaryEnabled
     * @param string $mode
     * @return DeployPackage
     */
    private function createService(
        bool $dictionaryEnabled = true,
        string $mode = AppState::MODE_PRODUCTION
    ): DeployPackage {
        $appState = $this->createStub(AppState::class);
        $appState->method('getMode')->willReturn($mode);
        return new DeployPackage(
            $appState,
            $this->createStub(LocaleResolver::class),
            $this->deployStaticFile,
            $this->logger,
            new JsTranslationConfig($dictionaryEnabled)
        );
    }
}
