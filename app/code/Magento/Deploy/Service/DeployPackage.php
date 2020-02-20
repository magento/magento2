<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Service;

use Magento\Deploy\Package\Package;
use Magento\Deploy\Package\PackageFile;
use Magento\Framework\App\State as AppState;
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
                $errorMessage = __('Compilation from source: ')
                    . $file->getSourcePath()
                    . PHP_EOL . $exception->getMessage() . PHP_EOL;
                $this->errorsCount++;
                $this->logger->critical($errorMessage);
                $package->deleteFile($file->getFileId());
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
     * @param PackageFile $file
     * @param Package $package
     * @param Package $parentPackage
     * @return bool
     */
    private function checkIfCanCopy(PackageFile $file, Package $package, Package $parentPackage = null)
    {
        return $parentPackage
        && $file->getOrigPackage() !== $package
        && (
            $file->getArea() !== $package->getArea()
            || $file->getTheme() !== $package->getTheme()
            || $file->getLocale() !== $package->getLocale()
        )
        && $file->getOrigPackage() === $parentPackage
        && $this->deployStaticFile->readFile($file->getDeployedFileId(), $parentPackage->getPath());
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
    private function register(Package $package, PackageFile $file = null, $skipLogging = false)
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
