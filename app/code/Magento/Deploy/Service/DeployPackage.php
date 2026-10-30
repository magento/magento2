<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Deploy\Service;

use Magento\Deploy\Package\Package;
use Magento\Deploy\Package\PackageFile;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\ResolverInterface as LocaleResolver;
use Magento\Framework\Translate\Js\Config as JsTranslationConfig;
use Magento\Framework\View\Asset\ContentProcessorException;
use Magento\Deploy\Console\InputValidator;
use Psr\Log\LoggerInterface;

/**
 * Deploy package service
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeployPackage
{
    /**
     * Application state object
     *
     * Allows to switch between different application areas
     *
     * @var AppState
     */
    private $appState;

    /**
     * Locale resolver interface
     *
     * Check if given locale code is a valid one
     *
     * @var LocaleResolver
     */
    private $localeResolver;

    /**
     * Service for deploying static files
     *
     * @var DeployStaticFile
     */
    private $deployStaticFile;

    /**
     * Logger interface
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Total count of processed files
     *
     * @var int
     */
    private $count = 0;

    /**
     * Total count of the errors
     *
     * @var int
     */
    private $errorsCount = 0;

    /**
     * @var JsTranslationConfig
     */
    private $jsTranslationConfig;

    /**
     * Whether the package being deployed resolves the same LESS/CSS sources as its parent
     *
     * @var bool
     */
    private $styleSourcesMatchParent = false;

    /**
     * DeployPackage constructor
     *
     * @param AppState $appState
     * @param LocaleResolver $localeResolver
     * @param DeployStaticFile $deployStaticFile
     * @param LoggerInterface $logger
     * @param JsTranslationConfig|null $jsTranslationConfig
     */
    public function __construct(
        AppState $appState,
        LocaleResolver $localeResolver,
        DeployStaticFile $deployStaticFile,
        LoggerInterface $logger,
        ?JsTranslationConfig $jsTranslationConfig = null
    ) {
        $this->appState = $appState;
        $this->localeResolver = $localeResolver;
        $this->deployStaticFile = $deployStaticFile;
        $this->logger = $logger;
        $this->jsTranslationConfig = $jsTranslationConfig
            ?? ObjectManager::getInstance()->get(JsTranslationConfig::class);
    }

    /**
     * Execute package deploy procedure
     *
     * @param Package $package
     * @param array $options
     * @param bool $skipLogging
     * @return bool true on success
     */
    public function deploy(Package $package, array $options, $skipLogging = false)
    {
        $result = $this->appState->emulateAreaCode(
            $package->getArea() === Package::BASE_AREA ? 'global' : $package->getArea(),
            function () use ($package, $options, $skipLogging) {
                // emulate application locale needed for correct file path resolving
                $this->localeResolver->setLocale($package->getLocale());
                $this->deployEmulated($package, $options, $skipLogging);
            }
        );
        $package->setState(Package::STATE_COMPLETED);
        return $result;
    }

    /**
     * Execute package deploy procedure when area already emulated
     *
     * @param Package $package
     * @param array $options
     * @param bool $skipLogging
     * @return bool
     */
    public function deployEmulated(Package $package, array $options, $skipLogging = false)
    {
        $this->count = 0;
        $this->errorsCount = 0;
        $parentPackage = $package->getParent();
        // In developer mode the Less processor embeds a source map with package specific paths
        $this->styleSourcesMatchParent = $parentPackage
            && $this->appState->getMode() !== AppState::MODE_DEVELOPER
            && $this->getStyleSources($package) === $this->getStyleSources($parentPackage);
        $this->register($package, null, $skipLogging);

        /** @var PackageFile $file */
        foreach ($package->getFiles() as $file) {
            $fileId = $file->getDeployedFileId();
            ++$this->count;
            $this->register($package, $file, $skipLogging);
            if ($this->checkFileSkip($fileId, $options)) {
                continue;
            }

            try {
                $this->processFile($file, $package);
            } catch (ContentProcessorException $exception) {
                $errorMessage = __(
                    'Compilation from source: %1',
                    $file->getSourcePath()
                    . PHP_EOL
                    . $exception->getMessage()
                    . PHP_EOL
                );
                $this->errorsCount++;
                $this->logger->critical($errorMessage);
                $package->deleteFile($file->getFileId());
                throw new LocalizedException($errorMessage);
            } catch (\Exception $exception) {
                $this->logger->critical(
                    'Compilation from source ' . $file->getSourcePath() . ' failed' . PHP_EOL . (string)$exception
                );
                $this->errorsCount++;
            }
        }

        // execute package post-processors (may adjust content of deployed files, or produce derivative files)
        foreach ($package->getPostProcessors() as $processor) {
            $processor->process($package, $options);
        }

        return true;
    }

    /**
     * Apply proper deployment action
     *
     * File can be created if content is already provided, or copied from parent package or published
     *
     * @param PackageFile $file
     * @param Package $package
     * @return void
     */
    private function processFile(PackageFile $file, Package $package)
    {
        if ($file->getContent()) {
            $this->deployStaticFile->writeFile(
                $file->getDeployedFileName(),
                $package->getPath(),
                $file->getContent()
            );
        } else {
            $parentPackage = $package->getParent();
            if ($this->checkIfCanCopy($file, $package, $parentPackage)) {
                $this->deployStaticFile->copyFile(
                    $file->getDeployedFileId(),
                    $parentPackage->getPath(),
                    $package->getPath()
                );
            } else {
                $this->deployStaticFile->deployFile(
                    $file->getFileName(),
                    [
                        'area' => $package->getArea(),
                        'theme' => $package->getTheme(),
                        'locale' => $package->getLocale(),
                        'module' => $file->getModule(),
                    ]
                );
            }
        }
    }

    /**
     * Check if file can be copied from parent package
     *
     * Copying is opt-in through the copy_from_parent param. A parent set by another strategy, such as the closest
     * ancestor in compact deploy, can hold output that was rewritten for its own scope.
     *
     * @see \Magento\Deploy\Strategy\QuickDeploy::addToQueue()
     * @param PackageFile $file
     * @param Package $package
     * @param Package $parentPackage
     * @return bool
     */
    private function checkIfCanCopy(PackageFile $file, Package $package, ?Package $parentPackage = null)
    {
        return $parentPackage
            && $package->getParam('copy_from_parent')
            && $file->getOrigPackage() !== $package
            && $this->isOutputPackageIndependent($file, $package)
            && $this->hasSameSourceInParent($file, $parentPackage)
            && $this->deployStaticFile->fileExists($file->getDeployedFileId(), $parentPackage->getPath());
    }

    /**
     * Check if parent package resolves the given file to the same source file
     *
     * @param PackageFile $file
     * @param Package $parentPackage
     * @return bool
     */
    private function hasSameSourceInParent(PackageFile $file, Package $parentPackage): bool
    {
        $parentFile = $parentPackage->getFile($file->getFileId());
        return $parentFile
            && $file->getSourcePath()
            && $parentFile->getSourcePath() === $file->getSourcePath();
    }

    /**
     * Check if processing the file gives the same result in the package and in its parent
     *
     * @param PackageFile $file
     * @param Package $package
     * @return bool
     */
    private function isOutputPackageIndependent(PackageFile $file, Package $package): bool
    {
        return match ($file->getExtension()) {
            // Compiled CSS depends on the whole @import graph resolved through theme and locale fallback, so the
            // full set of style sources decides. The file scope says nothing here: the Less and Css pre-processors
            // re-scope an inherited root to the package whenever the package holds any of its imports, which
            // aggregation guarantees.
            'less', 'css' => $this->styleSourcesMatchParent,
            // The embedded translation strategy writes theme and locale specific phrases into every JS file.
            // The framework Translate\Js\Config only exposes dictionaryEnabled(), so the "none" strategy also
            // gives false here. That is merely conservative, JS is not rewritten under "none". The Translation
            // module's isEmbeddedStrategy() is out of reach without a dependency Deploy does not have.
            'js' => $this->isCollectedForOtherScope($file, $package)
                && $this->jsTranslationConfig->dictionaryEnabled(),
            default => $this->isCollectedForOtherScope($file, $package),
        };
    }

    /**
     * Check if the file was collected for another area, theme or locale than the package
     *
     * @param PackageFile $file
     * @param Package $package
     * @return bool
     */
    private function isCollectedForOtherScope(PackageFile $file, Package $package): bool
    {
        return $file->getArea() !== $package->getArea()
            || $file->getTheme() !== $package->getTheme()
            || $file->getLocale() !== $package->getLocale();
    }

    /**
     * Get source paths of all LESS and CSS files of the package, keyed by file id
     *
     * @param Package $package
     * @return array
     */
    private function getStyleSources(Package $package): array
    {
        $sources = [];
        foreach (array_merge($package->getFilesByType('less'), $package->getFilesByType('css')) as $fileId => $file) {
            $sources[$fileId] = $file->getSourcePath();
        }
        ksort($sources);
        return $sources;
    }

    /**
     * Check if file can be deployed
     *
     * @param string $filePath
     * @param array $options
     * @return boolean
     */
    private function checkFileSkip($filePath, array $options)
    {
        if ($filePath !== '.') {
            $filePath = (string)$filePath;
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $basename = pathinfo($filePath, PATHINFO_BASENAME);
            if ($ext === 'less' && strpos($basename, '_') === 0) {
                return true;
            }
            $option = isset(InputValidator::$fileExtensionOptionMap[$ext])
                ? InputValidator::$fileExtensionOptionMap[$ext]
                : null;
            return $option ? (isset($options[$option]) ? $options[$option] : false) : false;
        }
        return false;
    }

    /**
     * Add operation to log and package info files
     *
     * @param Package $package
     * @param PackageFile|null $file
     * @param bool $skipLogging
     * @return void
     */
    private function register(Package $package, ?PackageFile $file = null, $skipLogging = false)
    {
        $info = [
            'count' => $this->count,
            'last' => $file ? $file->getSourcePath() : ''
        ];
        $this->deployStaticFile->writeTmpFile('info.json', $package->getPath(), json_encode($info));

        if (!$skipLogging) {
            $logMessage = '.';
            if ($file) {
                $logMessage = "Processing file '{$file->getSourcePath()}'";
                if ($file->getArea()) {
                    $logMessage .= "  for area '{$file->getArea()}'";
                }
                if ($file->getTheme()) {
                    $logMessage .= ", theme '{$file->getTheme()}'";
                }
                if ($file->getLocale()) {
                    $logMessage .= ", locale '{$file->getLocale()}'";
                }
                if ($file->getModule()) {
                    $logMessage .= "module '{$file->getModule()}'";
                }
            }

            $this->logger->info($logMessage);
        }
    }
}
