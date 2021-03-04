<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy;

use Magento\Deploy\Package\Processor\PreProcessor\Less;
use Magento\Deploy\Service\DeployStaticContent;
use Magento\Deploy\Strategy\DeployStrategyFactory;
use Magento\Framework\App\State;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Deploy\Console\DeployStaticOptions as Options;
use Magento\Framework\Config\View;
use Magento\Deploy\Config\BundleConfig;
use Magento\Framework\Filesystem\Directory\WriteInterface;

/**
 * Class DeployTest
 *
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
        Options::THEME => ['Magento/zoom1', 'Magento/zoom2', 'Magento/zoom3'],
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

        foreach (['Magento/zoom1', 'Magento/zoom2', 'Magento/zoom3'] as $theme) {
            $this->assertBundleSize($theme);
            $this->assertExcluded($theme, $this->config->getExcludedFiles());
            $this->assertExcluded($theme, $this->config->getExcludedDir());
        }
    }

    /**
     * Assert file exists in all themes and locales
     *
     * @param string $fileName
     * @return void
     */
    private function assertFileExistsIsGenerated($fileName)
    {
        foreach (['Magento/zoom1', 'Magento/zoom2', 'Magento/zoom3'] as $theme) {
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
     * Assert correct bundle size according to configuration set in view.xml
     *
     * @param string $theme
     * @return void
     */
    private function assertBundleSize($theme)
    {
        $expectedSize = $this->bundleConfig->getBundleFileMaxSize('frontend', $theme);
        $expectedSize *= 1.15;

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
