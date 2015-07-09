<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\App\View\Deployment\Version;
use Magento\Framework\App\View\Asset\Publisher;
use Magento\Framework\App\Utility\Files;
use Magento\Framework\Config\Theme;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Translate\Js\Config as JsTranslationConfig;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A service for deploying Magento static view files for production mode
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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

    /** @var bool */
    private $isDryRun;

    /** @var int */
    private $count;

    /** @var int */
    private $errorCount;

    /** @var \Magento\Framework\View\Template\Html\MinifierInterface */
    private $htmlMinifier;

    /** @var \Magento\Framework\View\Asset\MinifyService */
    protected $minifyService;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var JsTranslationConfig
     */
    protected $jsTranslationConfig;

    /**
     * @param Files $filesUtil
     * @param OutputInterface $output
     * @param Version\StorageInterface $versionStorage
     * @param \Magento\Framework\View\Asset\MinifyService $minifyService
     * @param JsTranslationConfig $jsTranslationConfig
     * @param bool $isDryRun
     */
    public function __construct(
        Files $filesUtil,
        OutputInterface $output,
        Version\StorageInterface $versionStorage,
        \Magento\Framework\View\Asset\MinifyService $minifyService,
        JsTranslationConfig $jsTranslationConfig,
        $isDryRun = false
    ) {
        $this->filesUtil = $filesUtil;
        $this->output = $output;
        $this->versionStorage = $versionStorage;
        $this->isDryRun = $isDryRun;
        $this->minifyService = $minifyService;
        $this->jsTranslationConfig = $jsTranslationConfig;
        $this->parentTheme = [];
    }

    /**
     * Populate all static view files for specified root path and list of languages
     *
     * @param ObjectManagerFactory $omFactory
     * @param array $locales
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function deploy(ObjectManagerFactory $omFactory, array $locales)
    {
        $this->omFactory = $omFactory;
        if ($this->isDryRun) {
            $this->output->writeln('Dry run. Nothing will be recorded to the target directory.');
        }
        $langList = implode(', ', $locales);
        $this->output->writeln("Requested languages: {$langList}");
        $libFiles = $this->filesUtil->getStaticLibraryFiles();
        list($areas, $appFiles) = $this->collectAppFiles($locales);
        foreach ($areas as $area => $themes) {
            $this->emulateApplicationArea($area);
            foreach ($locales as $locale) {
                $this->emulateApplicationLocale($locale, $area);
                foreach ($themes as $themePath) {
                    $this->output->writeln("=== {$area} -> {$themePath} -> {$locale} ===");
                    $this->count = 0;
                    $this->errorCount = 0;
                    foreach ($appFiles as $info) {
                        list($fileArea, $fileTheme, , $module, $filePath) = $info;
                        if (($fileArea == $area || $fileArea == 'base') &&
                            ($fileTheme == '' || $fileTheme == $themePath ||
                                in_array(
                                    $fileArea . Theme::THEME_PATH_SEPARATOR . $fileTheme,
                                    $this->findAncestors($area . Theme::THEME_PATH_SEPARATOR . $themePath)
                                ))
                        ) {
                            $this->deployFile($filePath, $area, $themePath, $locale, $module);
                        }
                    }
                    foreach ($libFiles as $filePath) {
                        $this->deployFile($filePath, $area, $themePath, $locale, null);
                    }
                    if ($this->jsTranslationConfig->dictionaryEnabled()) {
                        $this->deployFile(
                            $this->jsTranslationConfig->getDictionaryFileName(),
                            $area,
                            $themePath,
                            $locale,
                            null
                        );
                    }
                    $this->bundleManager->flush();
                    $this->output->writeln("\nSuccessful: {$this->count} files; errors: {$this->errorCount}\n---\n");
                }
            }
        }
        $this->output->writeln("=== Minify templates ===");
        $this->count = 0;
        foreach ($this->filesUtil->getPhtmlFiles(false, false) as $template) {
            $this->htmlMinifier->minify($template);
            if ($this->output->isVeryVerbose()) {
                $this->output->writeln($template . " minified\n");
            } else {
                $this->output->write('.');
            }
            $this->count++;
        }
        $this->output->writeln("\nSuccessful: {$this->count} files modified\n---\n");
        $version = (new \DateTime())->getTimestamp();
        $this->output->writeln("New version of deployed files: {$version}");
        if (!$this->isDryRun) {
            $this->versionStorage->save($version);
        }
    }

    /**
     * Accumulate all static view files in the application and record all found areas, themes and languages
     *
     * Returns an array of areas and files with meta information
     *
     * @param array $requestedLocales
     * @return array
     */
    private function collectAppFiles($requestedLocales)
    {
        $areas = [];
        $locales = [];
        $files = $this->filesUtil->getStaticPreProcessingFiles();
        foreach ($files as $info) {
            list($area, $themePath, $locale) = $info;
            if ($themePath) {
                $areas[$area][$themePath] = $themePath;
            }
            if ($locale) {
                $locales[$locale] = $locale;
            }
        }
        foreach ($requestedLocales as $locale) {
            unset($locales[$locale]);
        }
        if (!empty($locales)) {
            $langList = implode(', ', $locales);
            $this->output->writeln(
                "WARNING: there were files for the following languages detected in the file system: {$langList}."
                . ' These languages were not requested, so the files will not be populated.'
            );
        }

        return [$areas, $files];
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
            [\Magento\Framework\App\State::PARAM_MODE => \Magento\Framework\App\State::MODE_DEFAULT]
        );
        /** @var \Magento\Framework\App\State $appState */
        $appState = $this->objectManager->get('Magento\Framework\App\State');
        $appState->setAreaCode($areaCode);
        /** @var \Magento\Framework\App\ObjectManager\ConfigLoader $configLoader */
        $configLoader = $this->objectManager->get('Magento\Framework\App\ObjectManager\ConfigLoader');
        $this->objectManager->configure($configLoader->load($areaCode));
        $this->assetRepo = $this->objectManager->get('Magento\Framework\View\Asset\Repository');

        $this->assetPublisher = $this->objectManager->create('Magento\Framework\App\View\Asset\Publisher');
        $this->htmlMinifier = $this->objectManager->get('Magento\Framework\View\Template\Html\MinifierInterface');
        $this->bundleManager = $this->objectManager->get('Magento\Framework\View\Asset\Bundle\Manager');

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
        $translator = $this->objectManager->get('Magento\Framework\TranslateInterface');
        $translator->setLocale($locale);
        $translator->loadData($area, true);
    }

    /**
     * Deploy a static view file
     *
     * @param string $filePath
     * @param string $area
     * @param string $themePath
     * @param string $locale
     * @param string $module
     * @return void
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function deployFile($filePath, $area, $themePath, $locale, $module)
    {
        $requestedPath = $filePath;
        if (substr($filePath, -5) == '.less') {
            $requestedPath = preg_replace('/.less$/', '.css', $filePath);
        }
        $logMessage = "Processing file '$filePath' for area '$area', theme '$themePath', locale '$locale'";
        if ($module) {
            $logMessage .= ", module '$module'";
        }

        if ($this->output->isVeryVerbose()) {
            $this->output->writeln($logMessage);
        }

        try {
            $asset = $this->assetRepo->createAsset(
                $requestedPath,
                ['area' => $area, 'theme' => $themePath, 'locale' => $locale, 'module' => $module]
            );
            $asset = $this->minifyService->getAssets([$asset], true)[0];
            if ($this->output->isVeryVerbose()) {
                $this->output->writeln("\tDeploying the file to '{$asset->getPath()}'");
            } else {
                $this->output->write('.');
            }
            if ($this->isDryRun) {
                $asset->getContent();
            } else {
                $this->assetPublisher->publish($asset);
                $this->bundleManager->addAsset($asset);
            }
            $this->count++;
        } catch (\Less_Exception_Compiler $e) {
            $this->verboseLog(
                "\tNotice: Could not parse LESS file '$filePath'. "
                . "This may indicate that the file is incomplete, but this is acceptable. "
                . "The file '$filePath' will be combined with another LESS file."
            );
            $this->verboseLog("\tCompiler error: " . $e->getMessage());
        } catch (\Exception $e) {
            $this->output->writeln($e->getMessage() . " ($logMessage)");
            $this->verboseLog($e->getTraceAsString());
            $this->errorCount++;
        }
    }

    /**
     * Find ancestor themes' full paths
     *
     * @param string $themeFullPath
     * @return string[]
     */
    private function findAncestors($themeFullPath)
    {
        /** @var \Magento\Framework\View\Design\Theme\ListInterface $themeCollection */
        $themeCollection = $this->objectManager->get('Magento\Framework\View\Design\Theme\ListInterface');
        $theme = $themeCollection->getThemeByFullPath($themeFullPath);
        $ancestors = $theme->getInheritedThemes();
        $ancestorThemeFullPath = [];
        foreach ($ancestors as $ancestor) {
            $ancestorThemeFullPath[] = $ancestor->getFullPath();
        }
        return $ancestorThemeFullPath;
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
}
