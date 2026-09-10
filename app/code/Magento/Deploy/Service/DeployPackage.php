<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Deploy\Service;

use Magento\Deploy\Console\DeployStaticOptions;
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
     * Most worker processes to use for the stylesheets of a single package.
     *
     * Kept small deliberately. Deployment already runs one process per package, so these workers
     * compete with those; measured on a 12-package install, two workers took the deploy from
     * 6.82s to 5.04s while four gave 5.16s and eight 5.33s.
     */
    private const STYLESHEET_WORKERS = 2;

    /**
     * Fewest stylesheets a worker must have before starting it is worthwhile.
     */
    private const MIN_STYLESHEETS_PER_WORKER = 4;

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

        // Stylesheets are the expensive part of a package - LESS compilation - and each one is
        // written independently, so they can be produced by worker processes while the rest of
        // the package is deployed here in its original order.
        $useWorkers = $this->canUseStylesheetWorkers($options);
        /** @var PackageFile[] $stylesheets */
        $stylesheets = [];

        /** @var PackageFile $file */
        foreach ($package->getFiles() as $file) {
            $fileId = $file->getDeployedFileId();
            ++$this->count;
            $this->register($package, $file, $skipLogging);
            if ($this->checkFileSkip($fileId, $options)) {
                continue;
            }
            if ($useWorkers && pathinfo($file->getDeployedFileName(), PATHINFO_EXTENSION) === 'css') {
                $stylesheets[] = $file;
                continue;
            }

            $this->deployFileOrFail($file, $package);
        }

        // Anything the workers did not take - because there were too few to be worth a fork, or
        // because a worker failed - is deployed here, so failures are reported exactly as they
        // are on the sequential path.
        if ($stylesheets !== [] && !$this->deployStylesheetsInParallel($stylesheets, $package)) {
            foreach ($stylesheets as $file) {
                $this->deployFileOrFail($file, $package);
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
    /**
     * Deploy one file, reporting failures the way the deployment command expects.
     *
     * @param PackageFile $file
     * @param Package $package
     * @return void
     * @throws LocalizedException
     */
    private function deployFileOrFail(PackageFile $file, Package $package)
    {
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

    /**
     * Whether this package's stylesheets may be handed to worker processes.
     *
     * Deployment only forks when the operator asks for it with --jobs, so the same switch governs
     * these workers: without it a plain deploy would silently start forking.
     *
     * @param array $options
     * @return bool
     */
    private function canUseStylesheetWorkers(array $options)
    {
        $jobs = (int)($options[DeployStaticOptions::JOBS_AMOUNT] ?? DeployStaticOptions::DEFAULT_JOBS_AMOUNT);

        return $jobs > 1 && function_exists('pcntl_fork');
    }

    /**
     * Deploy stylesheets across worker processes.
     *
     * Returns false when workers were not used or one of them failed, leaving the files for the
     * caller to deploy; a failing build is re-run sequentially so the real compilation error is
     * reported rather than a worker exit status.
     *
     * @param PackageFile[] $files
     * @param Package $package
     * @return bool Whether every file was deployed by a worker
     */
    private function deployStylesheetsInParallel(array $files, Package $package)
    {
        $jobs = self::STYLESHEET_WORKERS;
        $workers = (int)min($jobs, intdiv(count($files), self::MIN_STYLESHEETS_PER_WORKER));
        if ($workers < 2) {
            return false;
        }

        $buckets = array_fill(0, $workers, []);
        foreach (array_values($files) as $index => $file) {
            $buckets[$index % $workers][] = $file;
        }

        $children = [];
        foreach ($buckets as $bucket) {
            if (!$bucket) {
                continue;
            }
            $pid = pcntl_fork();
            if ($pid === -1) {
                // Fork refused: reap what is running and let the caller deploy everything.
                $this->waitForWorkers($children);
                return false;
            }
            if ($pid === 0) {
                $status = 0;
                foreach ($bucket as $file) {
                    try {
                        $this->processFile($file, $package);
                    } catch (\Exception $exception) {
                        $status = 1;
                        break;
                    }
                }
                // phpcs:ignore Magento2.Security.LanguageConstruct.ExitUsage
                exit($status);
            }
            $children[] = $pid;
        }

        return $this->waitForWorkers($children) === 0;
    }

    /**
     * Reap every worker, returning how many did not succeed.
     *
     * @param int[] $children
     * @return int
     */
    private function waitForWorkers(array $children)
    {
        $failed = 0;
        foreach ($children as $pid) {
            $status = 0;
            do {
                $result = pcntl_waitpid($pid, $status);
                // Retry when the wait itself was interrupted by a signal.
            } while ($result === -1 && pcntl_get_last_error() === PCNTL_EINTR);

            if ($result !== $pid || !pcntl_wifexited($status) || pcntl_wexitstatus($status) !== 0) {
                ++$failed;
            }
        }

        return $failed;
    }

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
    private function checkIfCanCopy(PackageFile $file, Package $package, ?Package $parentPackage = null)
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
