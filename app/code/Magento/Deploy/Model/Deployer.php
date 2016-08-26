<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Deploy\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Asset\ContentProcessorException;
use Magento\Framework\View\Asset\PreProcessor\AlternativeSourceInterface;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\App\View\Deployment\Version;
use Magento\Framework\App\View\Asset\Publisher;
use Magento\Framework\App\Utility\Files;
use Magento\Framework\Config\Theme;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Translate\Js\Config as JsTranslationConfig;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\View\Asset\Minification;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Asset\ConfigInterface;
use Magento\Deploy\Console\Command\DeployStaticOptionsInterface as Options;

/**
 * A service for deploying Magento static view files for production mode
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Deployer
{
    /** @var Files */
    private $filesUtil;

    /** @var ObjectManagerFactory */
    private $omFactory;

    /** @var OutputInterface */
    private $output;

    /** @var Version\StorageInterface */
    private $versionStorage;

    /** @var \Magento\Framework\View\Asset\Repository */
    private $assetRepo;

    /** @var Publisher */
    private $assetPublisher;

    /** @var \Magento\Framework\View\Asset\Bundle\Manager */
    private $bundleManager;

    /** @var int */
    private $count;

    /** @var int */
    private $errorCount;

    /** @var \Magento\Framework\View\Template\Html\MinifierInterface */
    private $htmlMinifier;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var JsTranslationConfig
     */
    protected $jsTranslationConfig;

    /**
     * @var AlternativeSourceInterface[]
     */
    private $alternativeSources;

    /**
     * @var ThemeProviderInterface
     */
    private $themeProvider;

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
     * @var Minification
     */
    private $minification;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /** @var ConfigInterface */
    private $assetConfig;

    /**
     * @var array
     */
    private $options;

    /**
     * Constructor
     *
     * @param Files $filesUtil
     * @param OutputInterface $output
     * @param Version\StorageInterface $versionStorage
     * @param JsTranslationConfig $jsTranslationConfig
     * @param AlternativeSourceInterface[] $alternativeSources
     * @param array $options
     */
    public function __construct(
        Files $filesUtil,
        OutputInterface $output,
        Version\StorageInterface $versionStorage,
        JsTranslationConfig $jsTranslationConfig,
        array $alternativeSources,
        $options = []
    ) {
        $this->filesUtil = $filesUtil;
        $this->output = $output;
        $this->versionStorage = $versionStorage;
        $this->jsTranslationConfig = $jsTranslationConfig;
        if (is_array($options)) {
            $this->options = $options;
        } else {
            // backward compatibility support
            $this->options = [Options::DRY_RUN => (bool)$options];
        }
        $this->parentTheme = [];

        array_map(
            function (AlternativeSourceInterface $alternative) {
            },
            $alternativeSources
        );
        $this->alternativeSources = $alternativeSources;

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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
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
     * Populate all static view files for specified root path and list of languages
     *
     * @param ObjectManagerFactory $omFactory
     * @param array $locales
     * @param array $deployableAreaThemeMap
     * @return int
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function deploy(ObjectManagerFactory $omFactory, array $locales, array $deployableAreaThemeMap = [])
    {
        $this->omFactory = $omFactory;

        if ($this->getOption(Options::DRY_RUN)) {
            $this->output->writeln('Dry run. Nothing will be recorded to the target directory.');
        }
        $libFiles = $this->filesUtil->getStaticLibraryFiles();
        $appFiles = $this->filesUtil->getStaticPreProcessingFiles();

        foreach ($deployableAreaThemeMap as $area => $themes) {
            $this->emulateApplicationArea($area);
            foreach ($locales as $locale) {
                $this->emulateApplicationLocale($locale, $area);
                foreach ($themes as $themePath) {

                    $this->output->writeln("=== {$area} -> {$themePath} -> {$locale} ===");
                    $this->count = 0;
                    $this->errorCount = 0;

                    /** @var \Magento\Theme\Model\View\Design $design */
                    $design = $this->objectManager->create(\Magento\Theme\Model\View\Design::class);
                    $design->setDesignTheme($themePath, $area);

                    $assetRepo = $this->objectManager->create(
                        \Magento\Framework\View\Asset\Repository::class,
                        [
                            'design' => $design,
                        ]
                    );
                    /** @var \Magento\RequireJs\Model\FileManager $fileManager */
                    $fileManager = $this->objectManager->create(
                        \Magento\RequireJs\Model\FileManager::class,
                        [
                            'config' => $this->objectManager->create(
                                \Magento\Framework\RequireJs\Config::class,
                                [
                                    'assetRepo' => $assetRepo,
                                    'design' => $design,
                                ]
                            ),
                            'assetRepo' => $assetRepo,
                        ]
                    );
                    $fileManager->createRequireJsConfigAsset();

                    foreach ($appFiles as $info) {
                        list($fileArea, $fileTheme, , $module, $filePath, $fullPath) = $info;

                        if ($this->checkSkip($filePath)) {
                            continue;
                        }

                        if (($fileArea == $area || $fileArea == 'base') &&
                            ($fileTheme == '' || $fileTheme == $themePath ||
                                in_array(
                                    $fileArea . Theme::THEME_PATH_SEPARATOR . $fileTheme,
                                    $this->findAncestors($area . Theme::THEME_PATH_SEPARATOR . $themePath)
                                ))
                        ) {
                            $compiledFile = $this->deployFile(
                                $filePath,
                                $area,
                                $themePath,
                                $locale,
                                $module,
                                $fullPath
                            );
                            if ($compiledFile !== '') {
                                $this->deployFile($compiledFile, $area, $themePath, $locale, $module, $fullPath);
                            }
                        }
                    }
                    foreach ($libFiles as $filePath) {

                        if ($this->checkSkip($filePath)) {
                            continue;
                        }

                        $compiledFile = $this->deployFile($filePath, $area, $themePath, $locale, null);

                        if ($compiledFile !== '') {
                            $this->deployFile($compiledFile, $area, $themePath, $locale, null);
                        }
                    }
                    if (!$this->getOption(Options::NO_JAVASCRIPT)) {
                        if ($this->jsTranslationConfig->dictionaryEnabled()) {
                            $dictionaryFileName = $this->jsTranslationConfig->getDictionaryFileName();
                            $this->deployFile($dictionaryFileName, $area, $themePath, $locale, null);
                        }
                        if ($this->getMinification()->isEnabled('js')) {
                            $fileManager->createMinResolverAsset();
                        }
                    }
                    $this->bundleManager->flush();
                    $this->output->writeln("\nSuccessful: {$this->count} files; errors: {$this->errorCount}\n---\n");
                }
            }
        }
        if (!($this->getOption(Options::NO_HTML_MINIFY) ?: !$this->getAssetConfig()->isMinifyHtml())) {
            $this->output->writeln('=== Minify templates ===');
            $this->count = 0;
            foreach ($this->filesUtil->getPhtmlFiles(false, false) as $template) {
                $this->htmlMinifier->minify($template);
                if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                    $this->output->writeln($template . " minified\n");
                } else {
                    $this->output->write('.');
                }
                $this->count++;
            }
            $this->output->writeln("\nSuccessful: {$this->count} files modified\n---\n");
        }

        $version = (new \DateTime())->getTimestamp();
        $this->output->writeln("New version of deployed files: {$version}");
        if (!$this->getOption(Options::DRY_RUN)) {
            $this->versionStorage->save($version);
        }

        if ($this->errorCount > 0) {
            // we must have an exit code higher than zero to indicate something was wrong
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }

    /**
     * Get Minification instance
     *
     * @deprecated
     * @return Minification
     */
    private function getMinification()
    {
        if (null === $this->minification) {
            $this->minification = ObjectManager::getInstance()->get(Minification::class);
        }

        return $this->minification;
    }

    /**
     * Emulate application area and various services that are necessary for populating files
     *
     * @param string $areaCode
     * @return void
     */
    private function emulateApplicationArea($areaCode)
    {
        $this->objectManager = $this->omFactory->create(
            [\Magento\Framework\App\State::PARAM_MODE => \Magento\Framework\App\State::MODE_PRODUCTION]
        );
        /** @var \Magento\Framework\App\State $appState */
        $appState = $this->objectManager->get(\Magento\Framework\App\State::class);
        $appState->setAreaCode($areaCode);
        $this->assetRepo = $this->objectManager->get(\Magento\Framework\View\Asset\Repository::class);
        $this->assetPublisher = $this->objectManager->create(\Magento\Framework\App\View\Asset\Publisher::class);
        $this->htmlMinifier = $this->objectManager->get(\Magento\Framework\View\Template\Html\MinifierInterface::class);
        $this->bundleManager = $this->objectManager->get(\Magento\Framework\View\Asset\Bundle\Manager::class);
    }

    /**
     * Set application locale and load translation for area
     *
     * @param string $locale
     * @param string $area
     * @return void
     */
    protected function emulateApplicationLocale($locale, $area)
    {
        /** @var \Magento\Framework\TranslateInterface $translator */
        $translator = $this->objectManager->get(\Magento\Framework\TranslateInterface::class);
        $translator->setLocale($locale);
        $translator->loadData($area, true);
        /** @var \Magento\Framework\Locale\ResolverInterface $localeResolver */
        $localeResolver = $this->objectManager->get(\Magento\Framework\Locale\ResolverInterface::class);
        $localeResolver->setLocale($locale);
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
     * @throws \InvalidArgumentException
     * @throws LocalizedException
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
                $this->bundleManager->addAsset($asset);
            }
            $this->count++;
        } catch (ContentProcessorException $exception) {
            $pathInfo = $fullPath ?: $filePath;
            $errorMessage =  __('Compilation from source: ') . $pathInfo
                . PHP_EOL . $exception->getMessage();
            $this->errorCount++;
            $this->output->write(PHP_EOL . PHP_EOL . $errorMessage . PHP_EOL, true);

            $this->getLogger()->critical($errorMessage);
        } catch (\Exception $exception) {
            $this->output->write('.');
            $this->verboseLog($exception->getTraceAsString());
            $this->errorCount++;
        }

        return $compiledFile;
    }

    /**
     * Find ancestor themes' full paths
     *
     * @param string $themeFullPath
     * @return string[]
     */
    private function findAncestors($themeFullPath)
    {
        $theme = $this->getThemeProvider()->getThemeByFullPath($themeFullPath);
        $ancestors = $theme->getInheritedThemes();
        $ancestorThemeFullPath = [];
        foreach ($ancestors as $ancestor) {
            $ancestorThemeFullPath[] = $ancestor->getFullPath();
        }
        return $ancestorThemeFullPath;
    }


    /**
     * @return ThemeProviderInterface
     * @deprecated
     */
    private function getThemeProvider()
    {
        if (null === $this->themeProvider) {
            $this->themeProvider = ObjectManager::getInstance()->get(ThemeProviderInterface::class);
        }

        return $this->themeProvider;
    }

    /**
     * @return \Magento\Framework\View\Asset\ConfigInterface
     * @deprecated
     */
    private function getAssetConfig()
    {
        if (null === $this->assetConfig) {
            $this->assetConfig = ObjectManager::getInstance()->get(ConfigInterface::class);
        }
        return $this->assetConfig;
    }

    /**
     * Verbose log
     *
     * @param string $message
     * @return void
     */
    private function verboseLog($message)
    {
        if ($this->output->isVerbose()) {
            $this->output->writeln($message);
        }
    }

    /**
     * Retrieves LoggerInterface instance
     *
     * @return LoggerInterface
     * @deprecated
     */
    private function getLogger()
    {
        if (!$this->logger) {
            $this->logger = $this->objectManager->get(LoggerInterface::class);
        }

        return $this->logger;
    }
}
