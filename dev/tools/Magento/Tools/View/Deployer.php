<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Tools\View;

use Magento\TestFramework\Utility\Files;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\App\View\Deployment\Version;

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

    /** @var Version\GeneratorInterface */
    private $versionGenerator;

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
     * @param bool $isDryRun
     * @param \Magento\Framework\App\View\Deployment\Version\StorageInterface $versionStorage
     * @param \Magento\Framework\App\View\Deployment\Version\GeneratorInterface $versionGenerator
     */
    public function __construct(
        Files $filesUtil,
        Deployer\Log $logger,
        Version\StorageInterface $versionStorage,
        Version\GeneratorInterface $versionGenerator,
        $isDryRun = false
    ) {
        $this->filesUtil = $filesUtil;
        $this->logger = $logger;
        $this->versionStorage = $versionStorage;
        $this->versionGenerator = $versionGenerator;
        $this->isDryRun = $isDryRun;
    }

    /**
     * Populate all static view files for specified root path and list of languages
     *
     * @param string $rootPath
     * @param ObjectManagerFactory $omFactory
     * @param array $locales
     * @return void
     */
    public function deploy($rootPath, ObjectManagerFactory $omFactory, array $locales)
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
            $this->emulateApplicationArea($rootPath, $area);
            foreach ($locales as $locale) {
                foreach ($themes as $themePath) {
                    $this->logger->logMessage("=== {$area} -> {$themePath} -> {$locale} ===");
                    $this->count = 0;
                    $this->errorCount = 0;
                    foreach ($appFiles as $info) {
                        list($fileArea, $fileThemePath, , $module, $filePath) = $info;
                        $this->deployAppFile($area, $fileArea, $themePath, $fileThemePath, $locale, $module, $filePath);
                    }
                    foreach ($libFiles as $filePath) {
                        $this->deployFile($filePath, $area, $themePath, $locale, null);
                    }
                    $this->logger->logMessage("\nSuccessful: {$this->count} files; errors: {$this->errorCount}\n---\n");
                }
            }
        }
        $version = $this->versionGenerator->generate();
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
     * @param string $rootPath
     * @param string $areaCode
     * @return void
     */
    private function emulateApplicationArea($rootPath, $areaCode)
    {
        $objectManager = $this->omFactory->create(
            $rootPath,
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
     * Deploy a static view file that belongs to the application
     *
     * @param string $area
     * @param string $fileArea
     * @param string $themePath
     * @param string $fileThemePath
     * @param string $locale
     * @param string $module
     * @param string $filePath
     * @return void
     */
    private function deployAppFile($area, $fileArea, $themePath, $fileThemePath, $locale, $module, $filePath)
    {
        if ($fileArea && $fileArea != $area) {
            return;
        }
        if ($fileThemePath && $fileThemePath != $themePath) {
            return;
        }
        $this->deployFile($filePath, $area, $themePath, $locale, $module);
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
        $logModule = $module ? "<{$module}>" : (null === $module ? '<lib>' : '<theme>');
        try {
            $asset = $this->assetRepo->createAsset(
                $requestedPath,
                ['area' => $area, 'theme' => $themePath, 'locale' => $locale, 'module' => $module]
            );
            $this->logger->logDebug("{$logModule} {$filePath} -> {$asset->getPath()}");
            if ($this->isDryRun) {
                $asset->getContent();
            } else {
                $this->assetPublisher->publish($asset);
            }
            $this->count++;
        } catch (\Exception $e) {
            $this->logger->logError("{$logModule} {$filePath}");
            $this->logger->logDebug((string)$e);
            $this->errorCount++;
        }
    }
}
