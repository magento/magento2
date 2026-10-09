<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Deploy\Service;

use Magento\Deploy\Package\Package;
use Magento\Deploy\Package\PackageFile;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\ResolverInterface as LocaleResolver;
use Magento\Framework\View\Asset\ContentProcessorException;
use Magento\Deploy\Console\InputValidator;
use Psr\Log\LoggerInterface;

/**
 * Deploy package service
 */
class DeployPackage
{
    /**
     * Minimum ratio of inherited files required before a bulk directory copy is attempted.
     * Below this threshold the per-file loop is used instead to avoid unnecessary overhead.
     */
    private const BULK_COPY_THRESHOLD = 0.5;
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
     * DeployPackage constructor
     *
     * @param AppState $appState
     * @param LocaleResolver $localeResolver
     * @param DeployStaticFile $deployStaticFile
     * @param LoggerInterface $logger
     */
    public function __construct(
        AppState $appState,
        LocaleResolver $localeResolver,
        DeployStaticFile $deployStaticFile,
        LoggerInterface $logger
    ) {
        $this->appState = $appState;
        $this->localeResolver = $localeResolver;
        $this->deployStaticFile = $deployStaticFile;
        $this->logger = $logger;
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
        $parentBulkCopied = false;
        if ($parentPackage && $this->shouldBulkCopy($package, $parentPackage)) {
            $copiedCount = $this->deployStaticFile->copyTree(
                $parentPackage->getPath(),
                $package->getPath()
            );
            $parentBulkCopied = ($copiedCount > 0);
        }

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
                $this->processFile($file, $package, $parentBulkCopied);
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
     * @param PackageFile $file
     * @param Package $package
     * @param bool $parentBulkCopied
     * @return void
     */
    private function processFile(PackageFile $file, Package $package, bool $parentBulkCopied = false)
    {
        if ($file->getContent()) {
            $this->deployStaticFile->writeFile(
                $file->getDeployedFileName(),
                $package->getPath(),
                $file->getContent()
            );
            return;
        }

        $parentPackage = $package->getParent();

        if ($parentBulkCopied && $this->isInheritedFile($file, $package, $parentPackage)) {
            return;
        }

        if (!$parentBulkCopied && $this->checkIfCanCopy($file, $package, $parentPackage)) {
            $this->deployStaticFile->copyFile(
                $file->getDeployedFileId(),
                $parentPackage->getPath(),
                $package->getPath()
            );
            return;
        }

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

    /**
     * Check if file is inherited from a parent package.
     *
     * A file is inherited when it originates from a different scope (area/theme/locale) AND
     * exists in the parent's deployed file list. The parent file list check is required because
     * a scope mismatch alone is not enough — theme-specific files (e.g. critical.css in luma)
     * also have a locale mismatch but are not present in the parent theme's output.
     *
     * @param PackageFile $file
     * @param Package $package
     * @param Package|null $parentPackage
     * @return bool
     */
    private function isInheritedFile(PackageFile $file, Package $package, ?Package $parentPackage = null): bool
    {
        return $parentPackage
            && $file->getOrigPackage() !== $package
            && (
                $file->getArea() !== $package->getArea()
                || $file->getTheme() !== $package->getTheme()
                || $file->getLocale() !== $package->getLocale()
            )
            && isset($parentPackage->getFiles()[$file->getFileId()]);
    }

    /**
     * Check if a bulk directory copy is worth doing for this package.
     *
     * copyTree() is only faster than per-file copies when a large proportion of the package's
     * files are inherited — otherwise the directory traversal overhead exceeds the savings.
     * This uses the in-memory file lists to count without any filesystem calls.
     *
     * @param Package $package
     * @param Package $parentPackage
     * @return bool
     */
    private function shouldBulkCopy(Package $package, Package $parentPackage): bool
    {
        $packageFiles = $package->getFiles();
        $total = count($packageFiles);
        if ($total === 0) {
            return false;
        }

        $parentFiles = $parentPackage->getFiles();
        $inherited = 0;
        foreach ($packageFiles as $file) {
            if ($file->getContent()) {
                continue;
            }
            if ($file->getOrigPackage() !== $package
                && (
                    $file->getArea() !== $package->getArea()
                    || $file->getTheme() !== $package->getTheme()
                    || $file->getLocale() !== $package->getLocale()
                )
                && isset($parentFiles[$file->getFileId()])
            ) {
                $inherited++;
            }
        }

        return ($inherited / $total) >= self::BULK_COPY_THRESHOLD;
    }

    /**
     * Check if file can be copied from parent package
     *
     * @param PackageFile $file
     * @param Package $package
     * @param Package $parentPackage
     * @return bool
     */
    private function checkIfCanCopy(PackageFile $file, Package $package, ?Package $parentPackage = null)
    {
        return $parentPackage
            && $file->getOrigPackage() !== $package
            && (
                $file->getArea() !== $package->getArea()
                || $file->getTheme() !== $package->getTheme()
                || $file->getLocale() !== $package->getLocale()
            )
            && $this->deployStaticFile->fileExists($file->getDeployedFileId(), $parentPackage->getPath());
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
