<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Deploy;

use Magento\Deploy\Package\Processor\PreProcessor\Less;
use Magento\Deploy\Service\DeployStaticContent;
use Magento\Deploy\Strategy\DeployStrategyFactory;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\State;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Deploy\Console\DeployStaticOptions as Options;
use Magento\Framework\Config\View;
use Magento\Deploy\Config\BundleConfig;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Glob;
use Magento\Framework\View\Asset\Minification;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeployTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var WriteInterface
     */
    private $staticDir;

    /**
     * @var ReadInterface
     */
    private $rootDir;

    /**
     * @var DeployStaticContent
     */
    private $deployService;

    /**
     * @var View
     */
    private $config;

    /**
     * @var BundleConfig
     */
    private $bundleConfig;

    /**
     * @var DeployStaticContent
     */
    private $staticContentService;

    /**
     * @var string
     */
    private $prevMode;

    /**
     * @var array
     */
    private $options = [
        Options::DRY_RUN => false,
        Options::NO_JAVASCRIPT => false,
        Options::NO_JS_BUNDLE => false,
        Options::NO_CSS => false,
        Options::NO_LESS => false,
        Options::NO_IMAGES => false,
        Options::NO_FONTS => false,
        Options::NO_HTML => false,
        Options::NO_MISC => false,
        Options::NO_HTML_MINIFY => false,
        Options::AREA => ['frontend'],
        Options::EXCLUDE_AREA => ['none'],
        Options::THEME => ['Magento/zoom1', 'Magento/zoom2', 'Magento/zoom3', 'Vendor/parent', 'Vendor/child'],
        Options::EXCLUDE_THEME => ['none'],
        Options::LANGUAGE => ['en_US', 'fr_FR', 'pl_PL'],
        Options::EXCLUDE_LANGUAGE => ['none'],
        Options::JOBS_AMOUNT => 0,
        Options::SYMLINK_LOCALE => false,
        Options::STRATEGY => DeployStrategyFactory::DEPLOY_STRATEGY_COMPACT,
    ];

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->prevMode = $objectManager->get(State::class)->getMode();
        $objectManager->get(State::class)->setMode(State::MODE_PRODUCTION);

        $this->filesystem = $objectManager->get(Filesystem::class);
        $this->staticDir = $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
        $this->rootDir = $this->filesystem->getDirectoryRead(DirectoryList::ROOT);

        $logger = $objectManager->get(\Psr\Log\LoggerInterface::class);
        $this->deployService = $objectManager->create(
            DeployStaticContent::class,
            ['logger' => $logger]
        );

        $this->bundleConfig = $objectManager->create(BundleConfig::class);
        $this->config = $objectManager->create(View::class);

        $this->staticContentService = $objectManager->create(DeployStaticContent::class);

        $this->filesystem->getDirectoryWrite(DirectoryList::PUB)->delete(DirectoryList::STATIC_VIEW);
        $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)->delete(DirectoryList::TMP_MATERIALIZATION_DIR);
    }

    protected function tearDown(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get(State::class)->setMode($this->prevMode);
        $this->filesystem = $objectManager->get(Filesystem::class);
        $this->filesystem->getDirectoryWrite(DirectoryList::PUB)->delete(DirectoryList::STATIC_VIEW);
        $this->staticDir = $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
        $this->staticDir->getDriver()->createDirectory($this->staticDir->getAbsolutePath());

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Deploy/_files/theme.php
     */
    public function testDeploy()
    {
        $this->deployService->deploy($this->options);

        $this->assertFileExists($this->staticDir->getAbsolutePath('frontend/Magento/zoom1/default/css/root.css'));
        $this->assertFileExists($this->staticDir->getAbsolutePath('frontend/Magento/zoom2/default/css/root.css'));
        $this->assertFileExists($this->staticDir->getAbsolutePath('frontend/Magento/zoom3/default/css/root.css'));
        $this->assertFileExists($this->staticDir->getAbsolutePath('frontend/Magento/zoom3/default/css/local.css'));

        $this->assertFileExistsIsGenerated('requirejs-config.js');
        $this->assertFileExistsIsGenerated('requirejs-map.js');
        $this->assertFileExistsIsGenerated('map.json');
        $this->assertFileExistsIsGenerated('js-translation.json');
        $this->assertFileExistsIsGenerated('result_map.json');

        $actualFileContent = $this->staticDir->readFile('frontend/Magento/zoom3/default/css/root.css');
        $this->assertLessPreProcessor($actualFileContent);
        $this->assertCssUrlFixerPostProcessor($actualFileContent);

        $actualFileContent = $this->staticDir->readFile('frontend/Vendor/child/default/css/styles-m.css');
        $this->assertCssFromChildTheme($actualFileContent);

        foreach (['Magento/zoom1', 'Magento/zoom2', 'Magento/zoom3', 'Vendor/parent', 'Vendor/child'] as $theme) {
            $this->assertBundleSize($theme);
            $this->assertExcluded($theme, $this->config->getExcludedFiles());
            $this->assertExcluded($theme, $this->config->getExcludedDir());
        }
    }

    /**
     * Quick strategy may copy files from a parent package, so its output must match the standard strategy
     *
     * Vendor/child overrides a LESS source, an image and a JS file for fr_FR and, differently, for de_DE. The
     * collector creates both of those packages before the requested locales are added, so one of them is the base
     * locale package the other locales copy from and the other is a variant carrying overrides the base lacks.
     *
     * @magentoDataFixture Magento/Deploy/_files/theme.php
     * @param string[] $themes
     * @param string[] $locales
     * @param bool $noParent
     * @param bool $minify
     * @return void
     */
    #[DataProvider('quickDeployDataProvider')]
    public function testQuickDeployMatchesStandardDeploy(
        array $themes,
        array $locales,
        bool $noParent,
        bool $minify
    ): void {
        $options = array_replace(
            $this->options,
            [
                Options::THEME => $themes,
                Options::LANGUAGE => $locales,
                Options::NO_PARENT => $noParent,
            ]
        );

        if ($minify) {
            $this->setMinification(true);
        }
        try {
            $quickOutput = $this->deployVendorThemes($options, DeployStrategyFactory::DEPLOY_STRATEGY_QUICK, $minify);
            $standardOutput = $this->deployVendorThemes(
                $options,
                DeployStrategyFactory::DEPLOY_STRATEGY_STANDARD,
                $minify
            );
        } finally {
            if ($minify) {
                $this->setMinification(false);
                Bootstrap::getInstance()->reinitialize();
            }
        }

        $this->assertSame(
            $standardOutput,
            $quickOutput,
            "Quick deploy output differs from standard deploy output in:\n"
            . implode("\n", $this->getDifferentPaths($standardOutput, $quickOutput))
        );
    }

    /**
     * @return array
     */
    public static function quickDeployDataProvider(): array
    {
        $vendorThemes = ['Vendor/parent', 'Vendor/child'];
        $locales = ['en_US', 'fr_FR', 'de_DE'];

        return [
            'en_US first' => [$vendorThemes, $locales, false, false],
            'reversed locales' => [$vendorThemes, array_reverse($locales), false, false],
            'child theme only, no parent' => [['Vendor/child'], $locales, true, false],
            'css and js minified' => [$vendorThemes, $locales, false, true],
        ];
    }

    /**
     * Enable or remove CSS and JS minification in the default scope
     *
     * The application must be reinitialized afterwards: the configuration is read once per object manager.
     *
     * @param bool $enabled
     * @return void
     */
    private function setMinification(bool $enabled): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $configWriter = $objectManager->get(WriterInterface::class);
        foreach (['css', 'js'] as $contentType) {
            $path = sprintf(Minification::XML_PATH_MINIFICATION_ENABLED, $contentType);
            if ($enabled) {
                $configWriter->save($path, '1');
            } else {
                $configWriter->delete($path);
            }
        }
        $objectManager->get(CacheInterface::class)->clean([Config::CACHE_TAG]);
    }

    /**
     * Deploy from a clean state, check Vendor theme content and return sha256 of every file of the requested themes
     *
     * With --no-parent the standard strategy still writes RequireJS config, translation dictionaries and bundles for
     * the ancestor themes, the quick strategy does not, so only the requested themes are compared.
     *
     * @param array $options
     * @param string $strategy
     * @param bool $minify
     * @return array
     */
    private function deployVendorThemes(array $options, string $strategy, bool $minify): array
    {
        // Run each deployment as in its own process: packages, pre-processors, SRI hashes and globbed file lists are
        // all kept in memory by a previous deployment
        Bootstrap::getInstance()->reinitialize();
        Glob::clearCache();
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get(State::class)->setMode(State::MODE_PRODUCTION);
        $this->filesystem = $objectManager->get(Filesystem::class);
        $this->staticDir = $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
        $this->filesystem->getDirectoryWrite(DirectoryList::PUB)->delete(DirectoryList::STATIC_VIEW);
        $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)->delete(DirectoryList::TMP_MATERIALIZATION_DIR);

        $minification = $objectManager->get(Minification::class);
        foreach (['css', 'js'] as $contentType) {
            $this->assertSame(
                $minify,
                $minification->isEnabled($contentType),
                "{$strategy}: {$contentType} minification state is not the requested one"
            );
        }

        $objectManager->create(DeployStaticContent::class, ['logger' => $objectManager->get(LoggerInterface::class)])
            ->deploy(array_replace($options, [Options::STRATEGY => $strategy]));

        $this->assertVendorThemesContent($strategy, $options, $minification);
        $this->assertExcludedFromMinificationDeployed($strategy, $options, $minification);

        $hashes = [];
        foreach ($options[Options::THEME] as $theme) {
            /** @var \SplFileInfo $file */
            foreach ($this->getDirectoryIterator('frontend/' . $theme) as $file) {
                if ($file->isFile()) {
                    $hashes[$this->staticDir->getRelativePath($file->getPathname())] = hash_file(
                        'sha256',
                        $file->getPathname()
                    );
                }
            }
        }
        ksort($hashes);
        $this->assertNotEmpty($hashes, "No files deployed to frontend/Vendor with the {$strategy} strategy");

        return $hashes;
    }

    /**
     * Assert each Vendor package holds its own locale specific content
     *
     * @param string $strategy
     * @param array $options
     * @param Minification $minification
     * @return void
     */
    private function assertVendorThemesContent(string $strategy, array $options, Minification $minification): void
    {
        $markers = ['fr_FR' => 'fr-only-class-for-easy-find', 'de_DE' => 'de-only-class-for-easy-find'];
        $childMarkers = array_merge(['super-test-class-for-easy-find'], array_values($markers));
        foreach ($options[Options::LANGUAGE] as $locale) {
            $packagePath = 'frontend/Vendor/child/' . $locale . '/';
            $stylesPath = $minification->addMinifiedSign($packagePath . 'css/styles-m.css');
            $styles = $this->staticDir->readFile($stylesPath);
            $this->assertStringContainsString(
                'super-test-class-for-easy-find',
                $styles,
                "{$strategy}: {$stylesPath} misses the child theme style"
            );
            foreach ($markers as $markerLocale => $marker) {
                $message = "{$strategy}: {$stylesPath} has the wrong {$markerLocale} override state";
                if ($markerLocale === $locale) {
                    $this->assertStringContainsString($marker, $styles, $message);
                } else {
                    $this->assertStringNotContainsString($marker, $styles, $message);
                }
            }
            $this->assertChildLocaleFiles($strategy, $packagePath, $locale, isset($markers[$locale]), $minification);

            $parentStylesPath = $minification->addMinifiedSign(
                'frontend/Vendor/parent/' . $locale . '/css/styles-m.css'
            );
            if ($options[Options::NO_PARENT]) {
                $this->assertFileDoesNotExist(
                    $this->staticDir->getAbsolutePath($parentStylesPath),
                    "{$strategy}: {$parentStylesPath} is deployed although --no-parent is set"
                );
                continue;
            }
            $parentStyles = $this->staticDir->readFile($parentStylesPath);
            foreach ($childMarkers as $marker) {
                $this->assertStringNotContainsString(
                    $marker,
                    $parentStyles,
                    "{$strategy}: {$parentStylesPath} has the child theme style {$marker}"
                );
            }
        }
    }

    /**
     * Assert the child theme image and JS file of a locale are deployed from the locale override or the theme
     *
     * @param string $strategy
     * @param string $packagePath
     * @param string $locale
     * @param bool $hasOverrides
     * @param Minification $minification
     * @return void
     */
    private function assertChildLocaleFiles(
        string $strategy,
        string $packagePath,
        string $locale,
        bool $hasOverrides,
        Minification $minification
    ): void {
        $sourceDir = __DIR__ . '/_files/Vendor/child/web/' . ($hasOverrides ? 'i18n/' . $locale . '/' : '');
        $jsMarker = $hasOverrides ? $locale : 'default';
        foreach (['images/test-logo.svg', 'js/test-marker.js'] as $filePath) {
            $deployedPath = $minification->addMinifiedSign($packagePath . $filePath);
            $content = $this->staticDir->readFile($deployedPath);
            $message = "{$strategy}: {$deployedPath} is not deployed from {$sourceDir}{$filePath}";
            if ($deployedPath === $packagePath . $filePath) {
                $this->assertSame(file_get_contents($sourceDir . $filePath), $content, $message);
            } else {
                $this->assertMatchesRegularExpression(
                    '/vendorChildTestMarker\s*=\s*["\']' . $jsMarker . '["\']/',
                    $content,
                    $message
                );
            }
        }
    }

    /**
     * Assert library files excluded from minification keep their own name in every requested package
     *
     * HugeRTE ships both "x.js" and "x.min.js"; when minification is enabled the deployed name of "x.js" must still
     * be resolved from the full deployed path, which matches the "/hugerte/" exclude.
     *
     * @param string $strategy
     * @param array $options
     * @param Minification $minification
     * @return void
     */
    private function assertExcludedFromMinificationDeployed(
        string $strategy,
        array $options,
        Minification $minification
    ): void {
        foreach ($options[Options::THEME] as $theme) {
            foreach ($options[Options::LANGUAGE] as $locale) {
                foreach (['hugerte/hugerte.js', 'hugerte/skins/ui/hugerte-5/content.css'] as $filePath) {
                    $path = "frontend/{$theme}/{$locale}/{$filePath}";
                    $this->assertSame($path, $minification->addMinifiedSign($path), "{$path} is not excluded");
                    $this->assertFileExists(
                        $this->staticDir->getAbsolutePath($path),
                        "{$strategy}: {$path} is not deployed"
                    );
                }
            }
        }
    }

    /**
     * Return paths which exist in only one of the given maps or have a different hash
     *
     * @param array $expected
     * @param array $actual
     * @return string[]
     */
    private function getDifferentPaths(array $expected, array $actual): array
    {
        $paths = array_keys(array_diff_assoc($expected, $actual) + array_diff_assoc($actual, $expected));
        sort($paths);
        return $paths;
    }

    /**
     * Assert file exists in all themes and locales
     *
     * @param string $fileName
     * @return void
     */
    private function assertFileExistsIsGenerated($fileName)
    {
        foreach (['Magento/zoom1', 'Magento/zoom2', 'Magento/zoom3', 'Vendor/parent', 'Vendor/child'] as $theme) {
            foreach ($this->options[Options::LANGUAGE] as $locale) {
                $this->assertFileExists(
                    $this->staticDir->getAbsolutePath(
                        'frontend/' . $theme . '/' . $locale . '/' . $fileName
                    )
                );
            }
        }
    }

    /**
     * Assert Less pre-processor
     *
     * @see Less
     * @param $actualRootCssContent
     * @return void
     */
    private function assertLessPreProcessor($actualRootCssContent)
    {
        //_testA is included from Magento/zoom3
        //_testB is included from Magento/zoom2
        $this->assertStringContainsString('color:#111', $actualRootCssContent);
    }

    /**
     * Assert CssUrlFixer post-processor
     *
     * @param $actualRootCssContent
     * @return void
     */
    private function assertCssUrlFixerPostProcessor($actualRootCssContent)
    {
        //assert CssUrlFixer fix urls
        $this->assertStringContainsString(
            'url("../../../../../frontend/Magento/zoom1/default/images/logo-magento-1.png")',
            $actualRootCssContent
        );
        $this->assertStringContainsString(
            'url("../../../../../frontend/Magento/zoom2/default/images/logo-magento-2.png")',
            $actualRootCssContent
        );
        $this->assertStringContainsString(
            'url("../images/logo-magento-3.png")',
            $actualRootCssContent
        );
        //_testA is included from Magento/zoom3
        //_testB is included from Magento/zoom2
        $this->assertStringContainsString('color:#111', $actualRootCssContent);
    }

    /**
     * Assert CSS from child post-processor
     *
     * @param $actualRootCssContent
     * @return void
     */
    private function assertCssFromChildTheme($actualRootCssContent)
    {
        //assert CssUrlFixer fix urls
        $this->assertStringContainsString(
            'super-test-class-for-easy-find',
            $actualRootCssContent
        );
    }

    /**
     * Assert correct bundle size according to configuration set in view.xml
     *
     * @param string $theme
     * @return void
     */
    private function assertBundleSize($theme)
    {
        $expectedSize = $this->bundleConfig->getBundleFileMaxSize('frontend', $theme);
        $expectedSize *= 1.2;

        $iterator = $this->getDirectoryIterator("frontend/{$theme}/en_US/js/bundle");
        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $size = (int)$file->getSize() / 1024;
                $this->assertLessThan($expectedSize, $size);
            }
        }
    }

    /**
     * Assert that file is excluded from view.xml
     *
     * @param string $theme
     * @param array $excluded
     * @return void
     */
    private function assertExcluded($theme, array $excluded)
    {
        $iterator = $this->getDirectoryIterator("frontend/{$theme}/en_US/js/bundle");
        foreach ($excluded as $pathData) {
            $path = $this->splitPath($pathData);
            if ($path['module'] !== null) {
                $path = implode('/', $path);
            } else {
                $path = $path['path'];
            }
            /** @var \SplFileInfo $file */
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $bundleContent = $this->staticDir->readFile(
                        $this->staticDir->getRelativePath($file->getPathname())
                    );
                    $this->assertStringNotContainsString('"' . $path . '":"', $bundleContent);
                }
            }
        }
    }

    /**
     * @param string $path
     * @return \RecursiveIteratorIterator
     */
    private function getDirectoryIterator($path)
    {
        $dirIterator = new \RecursiveDirectoryIterator(
            $this->staticDir->getAbsolutePath($path),
            \FilesystemIterator::SKIP_DOTS
        );
        return new \RecursiveIteratorIterator($dirIterator, \RecursiveIteratorIterator::SELF_FIRST);
    }

    /**
     * Get excluded module and path from complex string
     *
     * @param string $path
     * @return array
     */
    private function splitPath($path)
    {
        if (strpos($path, '::') !== false) {
            list($module, $path) = explode('::', $path);
            return [
                'module' => $module != 'Lib' ? $module : null,
                'path' => $path,
            ];
        } else {
            return [
                'module' => null,
                'path' => $path,
            ];
        }
    }
}
