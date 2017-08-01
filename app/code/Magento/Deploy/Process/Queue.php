<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Process;

use Magento\Deploy\Package\Package;
use Magento\Deploy\Service\DeployPackage;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Locale\ResolverInterface as LocaleResolver;

/**
 * Deployment Queue
 *
 * Deploy packages in parallel forks (if available)
 * @since 2.2.0
 */
class Queue
{
    /**
     * Default max amount of processes
     */
    const DEFAULT_MAX_PROCESSES_AMOUNT = 4;

    /**
     * Default max execution time
     */
    const DEFAULT_MAX_EXEC_TIME = 400;

    /**
     * @var array
     * @since 2.2.0
     */
    private $packages = [];

    /**
     * @var int[]
     * @since 2.2.0
     */
    private $processIds = [];

    /**
     * @var Package[]
     * @since 2.2.0
     */
    private $inProgress = [];

    /**
     * @var int
     * @since 2.2.0
     */
    private $maxProcesses;

    /**
     * @var int
     * @since 2.2.0
     */
    private $maxExecTime;

    /**
     * @var AppState
     * @since 2.2.0
     */
    private $appState;

    /**
     * @var LocaleResolver
     * @since 2.2.0
     */
    private $localeResolver;

    /**
     * @var ResourceConnection
     * @since 2.2.0
     */
    private $resourceConnection;

    /**
     * @var LoggerInterface
     * @since 2.2.0
     */
    private $logger;

    /**
     * @var DeployPackage
     * @since 2.2.0
     */
    private $deployPackageService;

    /**
     * @var array
     * @since 2.2.0
     */
    private $options = [];

    /**
     * @var int
     * @since 2.2.0
     */
    private $start = 0;

    /**
     * @var int
     * @since 2.2.0
     */
    private $lastJobStarted = 0;

    /**
     * @param AppState $appState
     * @param LocaleResolver $localeResolver
     * @param ResourceConnection $resourceConnection
     * @param LoggerInterface $logger
     * @param DeployPackage $deployPackageService
     * @param array $options
     * @param int $maxProcesses
     * @param int $maxExecTime
     * @since 2.2.0
     */
    public function __construct(
        AppState $appState,
        LocaleResolver $localeResolver,
        ResourceConnection $resourceConnection,
        LoggerInterface $logger,
        DeployPackage $deployPackageService,
        array $options = [],
        $maxProcesses = self::DEFAULT_MAX_PROCESSES_AMOUNT,
        $maxExecTime = self::DEFAULT_MAX_EXEC_TIME
    ) {
        $this->appState = $appState;
        $this->localeResolver = $localeResolver;
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
        $this->deployPackageService = $deployPackageService;
        $this->options = $options;
        $this->maxProcesses = $maxProcesses;
        $this->maxExecTime = $maxExecTime;
    }

    /**
     * @param Package $package
     * @param Package[] $dependencies
     * @return bool true on success
     * @since 2.2.0
     */
    public function add(Package $package, array $dependencies = [])
    {
        $this->packages[$package->getPath()] = [
            'package' => $package,
            'dependencies' => $dependencies
        ];

        return true;
    }

    /**
     * @return Package[]
     * @since 2.2.0
     */
    public function getPackages()
    {
        return $this->packages;
    }

    /**
     * Process jobs
     *
     * @return int
     * @since 2.2.0
     */
    public function process()
    {
        $returnStatus = 0;
        $this->start = $this->lastJobStarted = time();
        $packages = $this->packages;
        while (count($packages) && $this->checkTimeout()) {
            foreach ($packages as $name => $packageJob) {
                $this->assertAndExecute($name, $packages, $packageJob);
            }
            $this->logger->notice('.');
            sleep(3);
            foreach ($this->inProgress as $name => $package) {
                if ($this->isDeployed($package)) {
                    unset($this->inProgress[$name]);
                }
            }
        }

        $this->awaitForAllProcesses();

        return $returnStatus;
    }

    /**
     * Check that all depended packages deployed and execute
     *
     * @param string $name
     * @param array $packages
     * @param array $packageJob
     * @return void
     * @since 2.2.0
     */
    private function assertAndExecute($name, array & $packages, array $packageJob)
    {
        /** @var Package $package */
        $package = $packageJob['package'];
        if ($package->getParent() && $package->getParent() !== $package) {
            foreach ($packageJob['dependencies'] as $dependencyName => $dependency) {
                if (!$this->isDeployed($dependency)) {
                    $this->assertAndExecute($dependencyName, $packages, $packages[$dependencyName]);
                }
            }
        }
        if (!$this->isDeployed($package)
            && ($this->maxProcesses < 2 || (count($this->inProgress) < $this->maxProcesses))) {
            unset($packages[$name]);
            $this->execute($package);
        }
    }

    /**
     * Need to wait till all processes finished
     *
     * @return void
     * @since 2.2.0
     */
    private function awaitForAllProcesses()
    {
        while ($this->inProgress && $this->checkTimeout()) {
            foreach ($this->inProgress as $name => $package) {
                if ($this->isDeployed($package)) {
                    unset($this->inProgress[$name]);
                }
            }
            $this->logger->notice('.');
            sleep(5);
        }
        if ($this->isCanBeParalleled()) {
            // close connections only if ran with forks
            $this->resourceConnection->closeConnection();
        }
    }

    /**
     * @return bool
     * @since 2.2.0
     */
    private function isCanBeParalleled()
    {
        return function_exists('pcntl_fork') && $this->maxProcesses > 1;
    }

    /**
     * @param Package $package
     * @return bool true on success for main process and exit for child process
     * @SuppressWarnings(PHPMD.ExitExpression)
     * @since 2.2.0
     */
    private function execute(Package $package)
    {
        $this->lastJobStarted = time();
        $this->logger->info(
            "Execute: " . $package->getPath(),
            [
                'process' => $package->getPath(),
                'count' => count($package->getFiles()),
            ]
        );

        $this->appState->emulateAreaCode(
            $package->getArea() == Package::BASE_AREA ? 'global' : $package->getArea(),
            function () use ($package) {
                // emulate application locale needed for correct file path resolving
                $this->localeResolver->setLocale($package->getLocale());

                // execute package pre-processors
                // (may add more files to deploy, so it needs to be executed in main thread)
                foreach ($package->getPreProcessors() as $processor) {
                    $processor->process($package, $this->options);
                }
            }
        );

        if ($this->isCanBeParalleled()) {
            $pid = pcntl_fork();
            if ($pid === -1) {
                throw new \RuntimeException('Unable to fork a new process');
            }

            if ($pid) {
                $this->inProgress[$package->getPath()] = $package;
                $this->processIds[$package->getPath()] = $pid;
                return true;
            }

            // process child process
            $this->inProgress = [];
            $this->deployPackageService->deploy($package, $this->options, true);
            exit(0);
        } else {
            $this->deployPackageService->deploy($package, $this->options);
            return true;
        }
    }

    /**
     * @param Package $package
     * @return bool
     * @since 2.2.0
     */
    private function isDeployed(Package $package)
    {
        if ($this->isCanBeParalleled()) {
            if ($package->getState() === null) {
                $pid = pcntl_waitpid($this->getPid($package), $status, WNOHANG);
                if ($pid === $this->getPid($package)) {
                    $package->setState(Package::STATE_COMPLETED);

                    unset($this->inProgress[$package->getPath()]);
                    return pcntl_wexitstatus($status) === 0;
                }
                return false;
            }
        }
        return $package->getState();
    }

    /**
     * @param Package $package
     * @return int|null
     * @since 2.2.0
     */
    private function getPid(Package $package)
    {
        return isset($this->processIds[$package->getPath()])
            ? $this->processIds[$package->getPath()]
            : null;
    }

    /**
     * @return bool
     * @since 2.2.0
     */
    private function checkTimeout()
    {
        return time() - $this->lastJobStarted < $this->maxExecTime;
    }

    /**
     * Free resources
     *
     * Protect against zombie process
     *
     * @return void
     * @since 2.2.0
     */
    public function __destruct()
    {
        foreach ($this->inProgress as $package) {
            if (pcntl_waitpid($this->getPid($package), $status) === -1) {
                throw new \RuntimeException(
                    'Error while waiting for package deployed: ' . $this->getPid($package) . '; Status: ' . $status
                );
            }
        }
    }
}
