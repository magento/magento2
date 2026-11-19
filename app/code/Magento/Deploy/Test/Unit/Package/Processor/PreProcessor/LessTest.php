<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Deploy\Test\Unit\Package\Processor\PreProcessor;

use Magento\Deploy\Console\DeployStaticOptions;
use Magento\Deploy\Package\Package;
use Magento\Deploy\Package\PackageFile;
use Magento\Deploy\Package\PackagePool;
use Magento\Deploy\Package\Processor\PreProcessor\Less;
use Magento\Deploy\Service\DeployStaticFile;
use Magento\Framework\View\Asset\Minification;
use Magento\Framework\View\Asset\NotationResolver\Module;
use Magento\Framework\View\Asset\PreProcessor\FileNameResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

/**
 * A child package recompiles a parent's root LESS file only when it overrides a file that root file imports,
 * directly or through other imports; otherwise the parent's compiled CSS is copied.
 */
class LessTest extends TestCase
{
    private const AREA = 'frontend';
    private const PARENT_THEME = 'Vendor/parent';
    private const CHILD_THEME = 'Vendor/child';
    private const LOCALE = 'en_US';
    private const ROOT_FILE = 'css/styles.less';
    private const ROOT_DEPLOYED_FILE = 'css/styles.css';
    private const OPTIONS = [DeployStaticOptions::NO_CSS => false];

    /**
     * LESS sources a deployment has published so far, by package path and file path
     *
     * @var array<string, array<string, string>>
     */
    private array $sources = [];

    /**
     * @var FileNameResolver&Stub
     */
    private $fileNameResolver;

    /**
     * @var DeployStaticFile&Stub
     */
    private $deployStaticFile;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->fileNameResolver = $this->createStub(FileNameResolver::class);
        $this->fileNameResolver->method('resolve')->willReturnCallback(
            static function (string $fileName): string {
                if (pathinfo($fileName, PATHINFO_EXTENSION) !== 'less' || str_starts_with(basename($fileName), '_')) {
                    return $fileName;
                }
                return substr($fileName, 0, -strlen('less')) . 'css';
            }
        );
        $this->deployStaticFile = $this->createStub(DeployStaticFile::class);
        $this->deployStaticFile->method('readTmpFile')->willReturnCallback(
            fn (string $fileName, string $packagePath): string|false => $this->readSource($fileName, $packagePath)
        );
    }

    /**
     * A partial reached two imports deep from the root file, overridden by the child, recompiles the root file
     * for the child: the child gets its own copy carrying the child's area, theme, locale and package.
     *
     * @return void
     */
    public function testOverrideReachedThroughImportsRecompilesRootFileForChild(): void
    {
        $parent = $this->createParentPackage(self::LOCALE, self::linearGraph());
        $parentRoot = $parent->getFile(self::ROOT_FILE);
        $child = $this->createChildPackage(self::LOCALE, $parent, ['css/source/_deep.less']);

        $this->assertTrue($this->createLess()->process($child, self::OPTIONS));

        $childRoot = $child->getFile(self::ROOT_FILE);
        $this->assertInstanceOf(PackageFile::class, $childRoot);
        $this->assertNotSame($parentRoot, $childRoot);
        $this->assertSame(self::AREA, $childRoot->getArea());
        $this->assertSame(self::CHILD_THEME, $childRoot->getTheme());
        $this->assertSame(self::LOCALE, $childRoot->getLocale());
        $this->assertSame($child, $childRoot->getPackage());
        $this->assertSame(
            ['area' => self::AREA, 'theme' => self::CHILD_THEME, 'locale' => self::LOCALE],
            $child->getMap()[self::ROOT_DEPLOYED_FILE] ?? null
        );
        $this->assertInstanceOf(PackageFile::class, $parentRoot);
        $this->assertSame(self::PARENT_THEME, $parentRoot->getTheme());
        $this->assertSame($parent, $parentRoot->getPackage());
    }

    /**
     * A child whose files are not reached from the root file gets no copy of it: the parent's CSS is copied.
     *
     * @return void
     */
    public function testOverrideNotReachedFromRootFileLeavesChildWithoutCopy(): void
    {
        $parent = $this->createParentPackage(self::LOCALE, self::linearGraph());
        $child = $this->createChildPackage(self::LOCALE, $parent, ['css/source/_elsewhere.less']);

        $this->assertTrue($this->createLess()->process($child, self::OPTIONS));

        $this->assertFalse($child->getFile(self::ROOT_FILE));
        $this->assertArrayNotHasKey(self::ROOT_DEPLOYED_FILE, $child->getMap());
    }

    /**
     * A child file whose deployed name is not known yet never counts as an override, and does not fail the deploy.
     *
     * @return void
     */
    public function testChildFileWithoutDeployedNameIsNoOverride(): void
    {
        $parent = $this->createParentPackage(self::LOCALE, self::linearGraph());
        $child = $this->createChildPackage(self::LOCALE, $parent, []);
        $file = new PackageFile('css/source/_deep.less', null, self::AREA, self::CHILD_THEME, self::LOCALE);
        $file->setPackage($child);

        $this->assertTrue($this->createLess()->process($child, self::OPTIONS));

        $this->assertFalse($child->getFile(self::ROOT_FILE));
    }

    /**
     * Two partials importing the same file, and a partial imported twice, give the same answers as a tree would.
     *
     * @param string $overriddenFile file the child overrides
     * @param bool $recompiled whether the root file must be recompiled for the child
     * @return void
     */
    #[DataProvider('diamondOverrideProvider')]
    public function testDiamondImportGraphAnswersByReachability(string $overriddenFile, bool $recompiled): void
    {
        $parent = $this->createParentPackage(self::LOCALE, self::diamondGraph());
        $child = $this->createChildPackage(self::LOCALE, $parent, [$overriddenFile]);

        $this->createLess()->process($child, self::OPTIONS);

        $this->assertSame($recompiled, $this->isRecompiledFor($child));
    }

    /**
     * Overridden files of the diamond graph and whether each one is reached from the root file
     *
     * @return array<string, array{string, bool}>
     */
    public static function diamondOverrideProvider(): array
    {
        return [
            'partial imported twice' => ['css/source/_left.less', true],
            'partial imported without extension' => ['css/source/_right.less', true],
            'file both partials import' => ['css/source/_shared.less', true],
            'file below the shared one' => ['css/source/_leaf.less', true],
            'file nothing imports' => ['css/source/_unrelated.less', false],
            'CSS file nothing imports' => ['css/unrelated.css', false],
        ];
    }

    /**
     * Imports are remembered by file path across every package one instance handles, and every package's copy
     * of a file adds its own imports: an import declared by either the earlier or the later package's copy of a
     * partial makes the later package's override count.
     *
     * @param bool $earlierImports whether the earlier package's copy of the partial declares the import
     * @param bool $laterImports whether the later package's copy of the partial declares the import
     * @return void
     */
    #[DataProvider('importDeclaredByProvider')]
    public function testImportsOfEveryPackageCountForLaterPackage(bool $earlierImports, bool $laterImports): void
    {
        $earlierParent = $this->createParentPackage('en_US', self::graphImportingExtra($earlierImports));
        $laterParent = $this->createParentPackage('de_DE', self::graphImportingExtra($laterImports));
        $earlierChild = $this->createChildPackage('en_US', $earlierParent, []);
        $laterChild = $this->createChildPackage('de_DE', $laterParent, ['css/source/_extra.less']);
        $less = $this->createLess();

        $less->process($earlierChild, self::OPTIONS);
        $less->process($laterChild, self::OPTIONS);

        $this->assertFalse($this->isRecompiledFor($earlierChild));
        $this->assertTrue($this->isRecompiledFor($laterChild));

        $freshChild = $this->createChildPackage('de_DE', $laterParent, ['css/source/_extra.less']);
        $this->createLess()->process($freshChild, self::OPTIONS);
        $this->assertSame($laterImports, $this->isRecompiledFor($freshChild));
    }

    /**
     * Which package's copy of the partial declares the import of the file the later child overrides
     *
     * @return array<string, array{bool, bool}>
     */
    public static function importDeclaredByProvider(): array
    {
        return [
            'only the earlier package' => [true, false],
            'only the later package' => [false, true],
        ];
    }

    /**
     * One instance handling many packages with the same parent graph gives each package its own decision,
     * whatever it decided for the packages before.
     *
     * @return void
     */
    public function testManyPackagesInSequenceEachGetTheirOwnDecision(): void
    {
        $less = $this->createLess();
        $expected = [];
        $actual = [];
        for ($index = 0; $index < 30; $index++) {
            $locale = sprintf('xx_%02d', $index);
            $overrides = $index % 2 === 0;
            $parent = $this->createParentPackage($locale, self::diamondGraph());
            $child = $this->createChildPackage(
                $locale,
                $parent,
                [$overrides ? 'css/source/_leaf.less' : 'css/source/_unrelated.less']
            );

            $less->process($child, self::OPTIONS);

            $expected[$locale] = $overrides;
            $actual[$locale] = $this->isRecompiledFor($child);
        }

        $this->assertSame($expected, $actual);
    }

    /**
     * With CSS deployment switched off the processor reports nothing to do and does not look at parent files.
     *
     * @return void
     */
    public function testNoCssOptionSkipsPackage(): void
    {
        $child = $this->createIsolatedChildPackage(self::AREA, self::CHILD_THEME);

        $this->assertFalse($this->createLess()->process($child, [DeployStaticOptions::NO_CSS => true]));
        $this->assertFalse($child->getFile(self::ROOT_FILE));
    }

    /**
     * A package of the base area or the base theme has no parent theme to copy from and is left as it is.
     *
     * @param string $area package area
     * @param string $theme package theme
     * @return void
     */
    #[DataProvider('basePackageProvider')]
    public function testBasePackageIsLeftAsItIs(string $area, string $theme): void
    {
        $child = $this->createIsolatedChildPackage($area, $theme);

        $this->assertTrue($this->createLess()->process($child, self::OPTIONS));
        $this->assertSame([], $child->getFiles());
    }

    /**
     * Packages that belong to the base area or the base theme
     *
     * @return array<string, array{string, string}>
     */
    public static function basePackageProvider(): array
    {
        return [
            'base area' => [Package::BASE_AREA, self::CHILD_THEME],
            'base theme' => [self::AREA, Package::BASE_THEME],
        ];
    }

    /**
     * Root file importing a partial that imports another partial
     *
     * @return array<string, string>
     */
    private static function linearGraph(): array
    {
        return [
            self::ROOT_FILE => "@import 'source/_middle.less';\n",
            'css/source/_middle.less' => "@import '_deep.less';\n",
            'css/source/_deep.less' => "@color: #000;\n",
        ];
    }

    /**
     * The linear graph, its middle partial importing one more file or not
     *
     * @param bool $importsExtra whether the middle partial imports the extra file
     * @return array<string, string>
     */
    private static function graphImportingExtra(bool $importsExtra): array
    {
        $graph = self::linearGraph();
        if ($importsExtra) {
            $graph['css/source/_middle.less'] .= "@import '_extra.less';\n";
        }
        return $graph;
    }

    /**
     * Root file importing two partials that both import a shared file, one partial imported twice
     *
     * @return array<string, string>
     */
    private static function diamondGraph(): array
    {
        return [
            self::ROOT_FILE => "@import 'source/_left.less';\n@import 'source/_right';\n@import 'source/_left.less';\n",
            'css/source/_left.less' => "@import '_shared.less';\n",
            'css/source/_right.less' => "@import '_shared';\n",
            'css/source/_shared.less' => "@import '_leaf.less';\n",
            'css/source/_leaf.less' => "@color: #000;\n",
            'css/source/_unrelated.less' => "@color: #fff;\n",
        ];
    }

    /**
     * Create the processor under test
     *
     * @return Less
     */
    private function createLess(): Less
    {
        return new Less(
            $this->fileNameResolver,
            $this->createStub(Module::class),
            $this->deployStaticFile,
            $this->createStub(Minification::class)
        );
    }

    /**
     * Create a parent theme package holding the given LESS files and publish their sources
     *
     * @param string $locale package locale
     * @param array<string, string> $lessSources LESS source by file path
     * @return Package
     */
    private function createParentPackage(string $locale, array $lessSources): Package
    {
        $package = new Package(
            $this->createStub(PackagePool::class),
            $this->fileNameResolver,
            self::AREA,
            self::PARENT_THEME,
            $locale
        );
        foreach ($lessSources as $fileName => $source) {
            $this->sources[$package->getPath()][$fileName] = $source;
            $this->addFile($package, $fileName, self::PARENT_THEME, $locale);
        }
        return $package;
    }

    /**
     * Create a child theme package whose parent files come from the given package
     *
     * @param string $locale package locale
     * @param Package $parent package the child inherits from
     * @param list<string> $overriddenFiles files the child theme provides itself
     * @return Package&MockObject
     */
    private function createChildPackage(string $locale, Package $parent, array $overriddenFiles): Package&MockObject
    {
        $package = $this->getMockBuilder(Package::class)
            ->setConstructorArgs(
                [$this->createStub(PackagePool::class), $this->fileNameResolver, self::AREA, self::CHILD_THEME, $locale]
            )
            ->onlyMethods(['getParentFiles'])
            ->getMock();
        $package->expects($this->atLeastOnce())->method('getParentFiles')->willReturnCallback(
            static fn (string $type): array => $parent->getFilesByType($type)
        );
        foreach ($overriddenFiles as $fileName) {
            $this->addFile($package, $fileName, self::CHILD_THEME, $locale);
        }
        return $package;
    }

    /**
     * Create a package that must not be asked for parent files
     *
     * @param string $area package area
     * @param string $theme package theme
     * @return Package&MockObject
     */
    private function createIsolatedChildPackage(string $area, string $theme): Package&MockObject
    {
        $package = $this->getMockBuilder(Package::class)
            ->setConstructorArgs(
                [$this->createStub(PackagePool::class), $this->fileNameResolver, $area, $theme, self::LOCALE]
            )
            ->onlyMethods(['getParentFiles'])
            ->getMock();
        $package->expects($this->never())->method('getParentFiles');
        return $package;
    }

    /**
     * Add a theme file to a package under the name it is deployed as
     *
     * @param Package $package package receiving the file
     * @param string $fileName file path inside the theme
     * @param string $theme theme the file belongs to
     * @param string $locale locale the file belongs to
     * @return void
     */
    private function addFile(Package $package, string $fileName, string $theme, string $locale): void
    {
        $file = new PackageFile($fileName, null, self::AREA, $theme, $locale);
        $file->setDeployedFileName($this->fileNameResolver->resolve($fileName));
        $file->setPackage($package);
    }

    /**
     * Source of a file as published for a package, false when it was not published
     *
     * @param string $fileName file path inside the theme
     * @param string $packagePath path of the package the file was published for
     * @return string|false
     */
    private function readSource(string $fileName, string $packagePath): string|false
    {
        return $this->sources[$packagePath][$fileName] ?? false;
    }

    /**
     * Whether the package holds its own copy of the root file, to be compiled from its own files
     *
     * @param Package $package child package
     * @return bool
     */
    private function isRecompiledFor(Package $package): bool
    {
        $file = $package->getFile(self::ROOT_FILE);
        return $file instanceof PackageFile && $file->getPackage() === $package;
    }
}
