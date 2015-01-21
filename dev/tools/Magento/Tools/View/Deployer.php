<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tools\View;

use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\App\View\Deployment\Version;
use Magento\Framework\Test\Utility\Files;

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

    /** @var Deployer\Log */
    private $logger;

    /** @var Version\StorageInterface */
    private $versionStorage;

    /** @var \Magento\Framework\Stdlib\DateTime */
    private $dateTime;

    /** @var \Magento\Framework\View\Asset\Repository */
    private $assetRepo;

    /** @var \Magento\Framework\App\View\Asset\Publisher */
    private $assetPublisher;

    /** @var bool */
    private $isDryRun;

    /** @var int */
    private $count;

    /** @var int */
    private $errorCount;

    /**
     * @param Files $filesUtil
     * @param Deployer\Log $logger
     * @param Version\StorageInterface $versionStorage
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param bool $isDryRun
     */
    public function __construct(
        Files $filesUtil,
        Deployer\Log $logger,
        Version\StorageInterface $versionStorage,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        $isDryRun = false
    ) {
        $this->filesUtil = $filesUtil;
        $this->logger = $logger;
        $this->versionStorage = $versionStorage;
        $this->dateTime = $dateTime;
        $this->isDryRun = $isDryRun;
    }

    /**
     * Populate all static view files for specified root path and list of languages
     *
     * @param ObjectManagerFactory $omFactory
     * @param array $locales
     * @return void
     */
    public function deploy(ObjectManagerFactory $omFactory, array $locales)
    {
        $this->omFactory = $omFactory;
        if ($this->isDryRun) {
            $this->logger->logMessage('Dry run. Nothing will be recorded to the target directory.');
        }
        $langList = implode(', ', $locales);
        $this->logger->logMessage("Requested languages: {$langList}");
        $libFiles = $this->filesUtil->getStaticLibraryFiles();
        list($areas, $appFiles) = $this->collectAppFiles($locales);
        foreach ($areas as $area => $themes) {
            $this->emulateApplicationArea($area);
            foreach ($locales as $locale) {
                foreach ($themes as $themePath) {
                    $this->logger->logMessage("=== {$area} -> {$themePath} -> {$locale} ===");
                    $this->count = 0;
                    $this->errorCount = 0;
                    foreach ($appFiles as $info) {
                        list(, , , $module, $filePath) = $info;
                        $this->deployFile($filePath, $area, $themePath, $locale, $module);
                    }
                    foreach ($libFiles as $filePath) {
                        $this->deployFile($filePath, $area, $themePath, $locale, null);
                    }
                    $this->logger->logMessage("\nSuccessful: {$this->count} files; errors: {$this->errorCount}\n---\n");
                }
            }
        }
        $version = $this->dateTime->toTimestamp(true);
        $this->logger->logMessage("New version of deployed files: {$version}");
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
            $this->logger->logMessage(
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
        $objectManager = $this->omFactory->create(
            [\Magento\Framework\App\State::PARAM_MODE => \Magento\Framework\App\State::MODE_DEFAULT]
        );
        /** @var \Magento\Framework\App\State $appState */
        $appState = $objectManager->get('Magento\Framework\App\State');
        $appState->setAreaCode($areaCode);
        /** @var \Magento\Framework\App\ObjectManager\ConfigLoader $configLoader */
        $configLoader = $objectManager->get('Magento\Framework\App\ObjectManager\ConfigLoader');
        $objectManager->configure($configLoader->load($areaCode));
        $this->assetRepo = $objectManager->get('Magento\Framework\View\Asset\Repository');
        $this->assetPublisher = $objectManager->get('Magento\Framework\App\View\Asset\Publisher');
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
        $this->logger->logDebug($logMessage);
        try {
            $asset = $this->assetRepo->createAsset(
                $requestedPath,
                ['area' => $area, 'theme' => $themePath, 'locale' => $locale, 'module' => $module]
            );
            $this->logger->logDebug("\tDeploying the file to '{$asset->getPath()}'", '.');
            if ($this->isDryRun) {
                $asset->getContent();
            } else {
                $this->assetPublisher->publish($asset);
            }
            $this->count++;
        } catch (\Magento\Framework\View\Asset\File\NotFoundException $e) {
            // File was not found by Fallback (possibly because it's wrong context for it) - there is nothing to publish
            $this->logger->logDebug(
                "\tNotice: Could not find file '$filePath'. This file may not be relevant for the theme or area."
            );
        } catch (\Less_Exception_Compiler $e) {
            $this->logger->logDebug(
                "\tNotice: Could not parse LESS file '$filePath'. "
                . "This may indicate that the file is incomplete, but this is acceptable. "
                . "The file '$filePath' will be combined with another LESS file."
            );
        } catch (\Exception $e) {
            $this->logger->logError($e->getMessage() . " ($logMessage)");
            $this->logger->logDebug((string)$e);
            $this->errorCount++;
        }
    }
}
