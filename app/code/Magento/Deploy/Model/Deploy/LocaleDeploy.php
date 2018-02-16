<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Deploy\Model\Deploy;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Utility\Files;
use Magento\Framework\App\View\Asset\Publisher;
use Magento\Framework\View\Asset\ContentProcessorException;
use Magento\Framework\View\Asset\PreProcessor\AlternativeSourceInterface;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Magento\Framework\View\Design\Theme\ListInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Config\Theme;
use Magento\Deploy\Console\Command\DeployStaticOptionsInterface as Options;
use Magento\Framework\Translate\Js\Config as JsTranslationConfig;
use Magento\Framework\View\Asset\Minification;
use Psr\Log\LoggerInterface;
use Magento\Framework\Console\Cli;

/**
 * Class which allows deploy by locales
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class LocaleDeploy implements DeployInterface
{
    /**
     * @var int
     */
    private $count = 0;

    /**
     * @var int
     */
    private $errorCount = 0;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    private $assetRepo;

    /**
     * @var Publisher
     */
    private $assetPublisher;

    /**
     * @var \Magento\Framework\View\Asset\Bundle\Manager
     */
    private $bundleManager;

    /**
     * @var Files
     */
    private $filesUtil;

    /**
     * @var ThemeProviderInterface
     */
    private $themeProvider;

    /**
     * @var array
     */
    private $options = [];

    /**
     * @var JsTranslationConfig
     */
    private $jsTranslationConfig;

    /**
     * @var Minification
     */
    private $minification;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\View\Asset\RepositoryFactory
     */
    private $assetRepoFactory;

    /**
     * @var \Magento\RequireJs\Model\FileManagerFactory
     */
    private $fileManagerFactory;

    /**
     * @var \Magento\Framework\RequireJs\ConfigFactory
     */
    private $requireJsConfigFactory;

    /**
     * @var \Magento\Framework\View\DesignInterfaceFactory
     */
    private $designFactory;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    private $localeResolver;

    /**
     * @var \Magento\Framework\View\Asset\PreProcessor\AlternativeSourceInterface[]
     */
    private $alternativeSources;

    /**
     * @var ListInterface
     */
    private $themeList;

    /**
     * @var array
     */
    private static $fileExtensionOptionMap = [
        'js' => Options::NO_JAVASCRIPT,
        'map' => Options::NO_JAVASCRIPT,
        'css' => Options::NO_CSS,
        'less' => Options::NO_LESS,
        'html' => Options::NO_HTML,
        'htm' => Options::NO_HTML,
        'jpg' => Options::NO_IMAGES,
        'jpeg' => Options::NO_IMAGES,
        'gif' => Options::NO_IMAGES,
        'png' => Options::NO_IMAGES,
        'ico' => Options::NO_IMAGES,
        'svg' => Options::NO_IMAGES,
        'eot' => Options::NO_FONTS,
        'ttf' => Options::NO_FONTS,
        'woff' => Options::NO_FONTS,
        'woff2' => Options::NO_FONTS,
        'md' => Options::NO_MISC,
        'jbf' => Options::NO_MISC,
        'csv' => Options::NO_MISC,
        'json' => Options::NO_MISC,
        'txt' => Options::NO_MISC,
        'htc' => Options::NO_MISC,
        'swf' => Options::NO_MISC,
        'LICENSE' => Options::NO_MISC,
        '' => Options::NO_MISC,
    ];

    /**
     * @param OutputInterface $output
     * @param JsTranslationConfig $jsTranslationConfig
     * @param Minification $minification
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\View\Asset\RepositoryFactory $assetRepoFactory
     * @param \Magento\RequireJs\Model\FileManagerFactory $fileManagerFactory
     * @param \Magento\Framework\RequireJs\ConfigFactory $requireJsConfigFactory
     * @param Publisher $assetPublisher
     * @param \Magento\Framework\View\Asset\Bundle\Manager $bundleManager
     * @param ThemeProviderInterface $themeProvider
     * @param LoggerInterface $logger
     * @param Files $filesUtil
     * @param \Magento\Framework\View\DesignInterfaceFactory $designFactory
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param array $alternativeSources
     * @param array $options
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        OutputInterface $output,
        JsTranslationConfig $jsTranslationConfig,
        Minification $minification,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\View\Asset\RepositoryFactory $assetRepoFactory,
        \Magento\RequireJs\Model\FileManagerFactory $fileManagerFactory,
        \Magento\Framework\RequireJs\ConfigFactory $requireJsConfigFactory,
        \Magento\Framework\App\View\Asset\Publisher $assetPublisher,
        \Magento\Framework\View\Asset\Bundle\Manager $bundleManager,
        \Magento\Framework\View\Design\Theme\ThemeProviderInterface $themeProvider,
        LoggerInterface $logger,
        Files $filesUtil,
        \Magento\Framework\View\DesignInterfaceFactory $designFactory,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        array $alternativeSources,
        $options = []
    ) {
        $this->output = $output;
        $this->assetRepo = $assetRepo;
        $this->assetPublisher = $assetPublisher;
        $this->bundleManager = $bundleManager;
        $this->filesUtil = $filesUtil;
        $this->jsTranslationConfig = $jsTranslationConfig;
        $this->minification = $minification;
        $this->logger = $logger;
        $this->assetRepoFactory = $assetRepoFactory;
        $this->fileManagerFactory = $fileManagerFactory;
        $this->requireJsConfigFactory = $requireJsConfigFactory;
        $this->themeProvider = $themeProvider;
        $this->alternativeSources = array_map(
            function (AlternativeSourceInterface $alternativeSource) {
                return $alternativeSource;
            },
            $alternativeSources
        );
        $this->designFactory = $designFactory;
        $this->localeResolver = $localeResolver;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function deploy($area, $themePath, $locale)
    {
        $this->output->writeln("=== {$area} -> {$themePath} -> {$locale} ===");

        // emulate application locale needed for correct file path resolving
        $this->localeResolver->setLocale($locale);

        $this->deployRequireJsConfig($area, $themePath);
        $this->deployAppFiles($area, $themePath, $locale);
        $this->deployLibFiles($area, $themePath, $locale);

        if (!$this->getOption(Options::NO_JAVASCRIPT)) {
            if ($this->jsTranslationConfig->dictionaryEnabled()) {
                $dictionaryFileName = $this->jsTranslationConfig->getDictionaryFileName();
                $this->deployFile($dictionaryFileName, $area, $themePath, $locale, null);
            }
        }
        if (!$this->getOption(Options::NO_JAVASCRIPT)) {
            $this->bundleManager->flush();
        }
        $this->output->writeln("\nSuccessful: {$this->count} files; errors: {$this->errorCount}\n---\n");

        return $this->errorCount ? Cli::RETURN_FAILURE : Cli::RETURN_SUCCESS;
    }

    /**
     * @param string $area
     * @param string $themePath
     * @return void
     */
    private function deployRequireJsConfig($area, $themePath)
    {
        if (!$this->getOption(Options::DRY_RUN) && !$this->getOption(Options::NO_JAVASCRIPT)) {

            /** @var \Magento\Framework\View\Design\ThemeInterface $theme */
            $theme = $this->getThemeList()->getThemeByFullPath($area . '/' . $themePath);
            $design = $this->designFactory->create()->setDesignTheme($theme, $area);
            $assetRepo = $this->assetRepoFactory->create(['design' => $design]);
            /** @var \Magento\RequireJs\Model\FileManager $fileManager */
            $fileManager = $this->fileManagerFactory->create(
                [
                    'config' => $this->requireJsConfigFactory->create(
                        [
                            'assetRepo' => $assetRepo,
                            'design' => $design,
                        ]
                    ),
                    'assetRepo' => $assetRepo,
                ]
            );
            $fileManager->createRequireJsConfigAsset();
            if ($this->minification->isEnabled('js')) {
                $fileManager->createMinResolverAsset();
            }
        }
    }

    /**
     * @param string $area
     * @param string $themePath
     * @param string $locale
     * @return void
     */
    private function deployAppFiles($area, $themePath, $locale)
    {
        foreach ($this->filesUtil->getStaticPreProcessingFiles() as $info) {
            list($fileArea, $fileTheme, , $module, $filePath, $fullPath) = $info;

            if ($this->checkSkip($filePath)) {
                continue;
            }

            if ($this->isCanBeDeployed($fileArea, $fileTheme, $area, $themePath)) {
                $compiledFile = $this->deployFile(
                    $filePath,
                    $area,
                    $themePath,
                    $locale,
                    $module,
                    $fullPath
                );
                if ($compiledFile !== '' && !$this->checkSkip($compiledFile)) {
                    $this->deployFile($compiledFile, $area, $themePath, $locale, $module, $fullPath);
                }
            }
        }
    }

    /**
     * @param string $fileArea
     * @param string $fileTheme
     * @param string $area
     * @param string $themePath
     * @return bool
     */
    private function isCanBeDeployed($fileArea, $fileTheme, $area, $themePath)
    {
        return ($fileArea == $area || $fileArea == 'base')
        && ($fileTheme == '' || $fileTheme == $themePath
            || in_array(
                $fileArea . Theme::THEME_PATH_SEPARATOR . $fileTheme,
                $this->findAncestors($area . Theme::THEME_PATH_SEPARATOR . $themePath)
            )
        );
    }

    /**
     * @param string $area
     * @param string $themePath
     * @param string $locale
     * @return void
     */
    private function deployLibFiles($area, $themePath, $locale)
    {
        foreach ($this->filesUtil->getStaticLibraryFiles() as $filePath) {

            if ($this->checkSkip($filePath)) {
                continue;
            }

            $compiledFile = $this->deployFile($filePath, $area, $themePath, $locale, null);

            if ($compiledFile !== '' && !$this->checkSkip($compiledFile)) {
                $this->deployFile($compiledFile, $area, $themePath, $locale, null);
            }
        }
    }

    /**
     * Deploy a static view file
     *
     * @param string $filePath
     * @param string $area
     * @param string $themePath
     * @param string $locale
     * @param string $module
     * @param string|null $fullPath
     * @return string
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function deployFile($filePath, $area, $themePath, $locale, $module, $fullPath = null)
    {
        $compiledFile = '';
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        foreach ($this->alternativeSources as $name => $alternative) {
            if (in_array($extension, $alternative->getAlternativesExtensionsNames(), true)
                && strpos(basename($filePath), '_') !== 0
            ) {
                $compiledFile = substr($filePath, 0, strlen($filePath) - strlen($extension) - 1);
                $compiledFile = $compiledFile . '.' . $name;
            }
        }

        if ($this->output->isVeryVerbose()) {
            $logMessage = "Processing file '$filePath' for area '$area', theme '$themePath', locale '$locale'";
            if ($module) {
                $logMessage .= ", module '$module'";
            }
            $this->output->writeln($logMessage);
        }

        try {
            $asset = $this->assetRepo->createAsset(
                $filePath,
                ['area' => $area, 'theme' => $themePath, 'locale' => $locale, 'module' => $module]
            );
            if ($this->output->isVeryVerbose()) {
                $this->output->writeln("\tDeploying the file to '{$asset->getPath()}'");
            } else {
                $this->output->write('.');
            }
            if ($this->getOption(Options::DRY_RUN)) {
                $asset->getContent();
            } else {
                $this->assetPublisher->publish($asset);
                if (!$this->getOption(Options::NO_JAVASCRIPT)) {
                    $this->bundleManager->addAsset($asset);
                }
            }
            $this->count++;
        } catch (ContentProcessorException $exception) {
            $pathInfo = $fullPath ?: $filePath;
            $errorMessage =  __('Compilation from source: ') . $pathInfo . PHP_EOL . $exception->getMessage();
            $this->errorCount++;
            $this->output->write(PHP_EOL . PHP_EOL . $errorMessage . PHP_EOL, true);

            $this->logger->critical($errorMessage);
        } catch (\Exception $exception) {
            $this->output->write('.');
            if ($this->output->isVerbose()) {
                $this->output->writeln($exception->getTraceAsString());
            }
            $this->errorCount++;
        }

        return $compiledFile;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    private function getOption($name)
    {
        return isset($this->options[$name]) ? $this->options[$name] : null;
    }

    /**
     * Check if skip flag is affecting file by extension
     *
     * @param string $filePath
     * @return boolean
     */
    private function checkSkip($filePath)
    {
        if ($filePath != '.') {
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $option = isset(self::$fileExtensionOptionMap[$ext]) ? self::$fileExtensionOptionMap[$ext] : null;

            return $option ? $this->getOption($option) : false;
        }

        return false;
    }

    /**
     * Find ancestor themes' full paths
     *
     * @param string $themeFullPath
     * @return string[]
     */
    private function findAncestors($themeFullPath)
    {
        $theme = $this->themeProvider->getThemeByFullPath($themeFullPath);
        $ancestors = $theme->getInheritedThemes();
        $ancestorThemeFullPath = [];
        foreach ($ancestors as $ancestor) {
            $ancestorThemeFullPath[] = $ancestor->getFullPath();
        }
        return $ancestorThemeFullPath;
    }

    /**
     * @deprecated
     * @return ListInterface
     */
    private function getThemeList()
    {
        if ($this->themeList === null) {
            $this->themeList = ObjectManager::getInstance()->get(ListInterface::class);
        }
        return $this->themeList;
    }
}
