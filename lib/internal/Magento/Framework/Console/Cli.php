<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Console;

use Laminas\ServiceManager\ServiceManager;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ProductMetadata;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Composer\ComposerJsonFinder;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Console\CommandLoader\Aggregate;
use Magento\Framework\Console\Exception\GenerationDirectoryAccessException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Shell\ComplexParameter;
use Magento\Setup\Application;
use Magento\Setup\Console\CommandLoader as SetupCommandLoader;
use Magento\Setup\Console\CompilerPreparation;
use Magento\Setup\Model\ObjectManagerProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console;

/**
 * Magento 2 CLI Application.
 *
 * This is the hood for all command line tools supported by Magento.
 *
 * @api
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Cli extends Console\Application
{
    /**
     * Name of input option.
     */
    public const INPUT_KEY_BOOTSTRAP = 'bootstrap';

    /**#@+
     * Cli exit codes.
     */
    public const RETURN_SUCCESS = 0;
    public const RETURN_FAILURE = 1;
    /**#@-*/

    /**
     * @var ServiceManager
     */
    private $serviceManager;

    /**
     * Initialization exception.
     *
     * @var \Exception
     */
    private $initException;

    /**
     * Exception that occurred during command initialization.
     *
     * @var \Exception
     */
    private $getCommandsException;

    /**
     * Failed commands during loading
     *
     * @var array
     */
    private $failedCommands = [];

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param string $name the application name
     * @param string $version the application version
     */
    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        try {
            // phpcs:ignore Magento2.Security.IncludeFile
            $configuration = require BP . '/setup/config/application.config.php';
            $bootstrapApplication = new Application();
            $application = $bootstrapApplication->bootstrap($configuration);
            $this->serviceManager = $application->getServiceManager();

            $this->assertCompilerPreparation();
            $this->initObjectManager();
        } catch (\Exception $exception) {
            $output = new \Symfony\Component\Console\Output\ConsoleOutput();
            $output->writeln(
                '<error>' . $exception->getMessage() . '</error>'
            );
            // phpcs:disable
            // phpcs:ignore Magento2.Security.LanguageConstruct.ExitUsage
            exit(static::RETURN_FAILURE);
            // phpcs:enable
        }

        if ($version == 'UNKNOWN') {
            $directoryList = new DirectoryList(BP);
            $composerJsonFinder = new ComposerJsonFinder($directoryList);
            $productMetadata = new ProductMetadata($composerJsonFinder);
            $version = $productMetadata->getVersion();
        }

        parent::__construct($name, $version);
        $this->serviceManager->setService(\Symfony\Component\Console\Application::class, $this);
        $this->logger = $this->objectManager->get(LoggerInterface::class);
        $this->setCommandLoader($this->getCommandLoader());
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @throws \Exception The exception in case of unexpected error
     */
    public function doRun(Console\Input\InputInterface $input, Console\Output\OutputInterface $output): int
    {
        $exitCode = null;
        try {
            $exitCode = parent::doRun($input, $output);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage() . PHP_EOL . $e->getTraceAsString();

            if ($this->getCommandsException) {
                // Command loading exception occurred earlier
                if ($this->isDeveloperMode()) {
                    // Developer mode: provide detailed error about command loading failure
                    $combinedErrorMessage = "Exception during console commands initialization: " .
                        $this->getCommandsException->getMessage() . PHP_EOL;
                    $this->initException = new \Exception($combinedErrorMessage, $e->getCode(), $e);
                    try {
                        if ($this->logger) {
                            $this->logger->error($combinedErrorMessage);
                        }
                    } catch (\Exception $logEx) {
                        error_log($combinedErrorMessage);
                    }
                } else {
                    // Production mode: command loading errors were already logged
                    // The specific command user tried to run may not be available
                    $warningMessage = PHP_EOL
                        . '<comment>Warning: Some commands failed to load due to errors.</comment>' . PHP_EOL
                        . '<comment>Check error logs (var/log/system.log) for details.</comment>' . PHP_EOL
                        . '<comment>The command you tried to run may not be available. '
                        . 'Try running: bin/magento list</comment>' . PHP_EOL;

                    try {
                        $output->writeln($warningMessage);
                    } catch (\Exception $outputEx) {
                        error_log(strip_tags($warningMessage));
                    }

                    // Return failure since the command couldn't be executed
                    return self::RETURN_FAILURE;
                }
            } else {
                // Not a command loading exception, handle normally
                $this->initException = $e;
                try {
                    if ($this->logger) {
                        $this->logger->error($errorMessage);
                    }
                } catch (\Exception $logEx) {
                    error_log($errorMessage);
                }
            }
        }

        if ($this->initException) {
            throw $this->initException;
        }

        return $exitCode !== null ? $exitCode : self::RETURN_SUCCESS;
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultCommands():array
    {
        return array_merge(parent::getDefaultCommands(), $this->getApplicationCommands());
    }

    /**
     * Gets application commands.
     *
     * @return array a list of available application commands
     */
    protected function getApplicationCommands()
    {
        $commands = [];

        // Load core Magento commands (try-catch to continue if this fails)
        try {
            if ($this->objectManager->get(DeploymentConfig::class)->isAvailable()) {
                /** @var CommandListInterface */
                $commandList = $this->objectManager->create(CommandListInterface::class);
                $commands = array_merge($commands, $commandList->getCommands());
            }
        } catch (\Exception $e) {
            $this->handleCommandLoadingException('core Magento commands', $e);
        }

        // Load vendor commands (already has its own try-catch internally)
        try {
            $commands = array_merge(
                $commands,
                $this->getVendorCommands($this->objectManager)
            );
        } catch (\Exception $e) {
            $this->handleCommandLoadingException('vendor commands', $e);
        }

        return $commands;
    }

    /**
     * Handle exception during command loading
     *
     * @param string $commandType
     * @param \Exception $e
     * @return void
     */
    private function handleCommandLoadingException(string $commandType, \Exception $e): void
    {
        // Store exception
        $this->getCommandsException = $e;

        // Developer mode: fail immediately with clear exception
        if ($this->isDeveloperMode()) {
            $this->initException = $e;
            throw $e;
        }

        // Production mode: log detailed error and continue
        $errorMessage = sprintf(
            'Failed to load %s: %s in %s:%d',
            $commandType,
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        );

        // Special handling for core commands failure - this is critical
        if ($commandType === 'core Magento commands') {
            $errorMessage .= PHP_EOL . PHP_EOL . "CRITICAL: Core Magento commands "
                . "(cache:flush, deploy:mode:set, etc.) are unavailable!";
            $errorMessage .= PHP_EOL . "This usually happens when a custom module "
                . "injects a broken command into di.xml.";
            $errorMessage .= PHP_EOL . PHP_EOL . "TO FIX THIS IMMEDIATELY:";
            $errorMessage .= PHP_EOL . "1. Run: rm -rf generated/code var/cache var/page_cache";
            $errorMessage .= PHP_EOL . "2. If problem persists, check var/log/system.log for the broken class";
            $errorMessage .= PHP_EOL . "3. Disable the problematic module or fix the command class";
        }

        // Ensure the error is logged to both Magento logs and PHP error log
        $this->logCommandLoadingError($errorMessage, $e);
    }

    /**
     * Object Manager initialization.
     *
     * @return void
     */
    private function initObjectManager()
    {
        $params = (new ComplexParameter(self::INPUT_KEY_BOOTSTRAP))->mergeFromArgv($_SERVER, $_SERVER);
        $params[Bootstrap::PARAM_REQUIRE_MAINTENANCE] = null;
        $requestParams = $this->serviceManager->get('magento-init-params');
        $appBootstrapKeys = [
            Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS,
            AppState::PARAM_MODE,
        ];

        foreach ($appBootstrapKeys as $appBootstrapKey) {
            if (isset($requestParams[$appBootstrapKey]) && !isset($params[$appBootstrapKey])) {
                $params[$appBootstrapKey] = $requestParams[$appBootstrapKey];
            }
        }

        $this->objectManager = Bootstrap::create(BP, $params)->getObjectManager();

        /** @var ObjectManagerProvider $omProvider */
        $omProvider = $this->serviceManager->get(ObjectManagerProvider::class);
        $omProvider->setObjectManager($this->objectManager);
    }

    /**
     * Checks whether compiler is being prepared.
     *
     * @return void
     * @throws GenerationDirectoryAccessException If generation directory is read-only
     */
    private function assertCompilerPreparation()
    {
        /**
         * Temporary workaround until the compiler is able to clear the generation directory
         * @todo remove after MAGETWO-44493 resolved
         */
        if (class_exists(CompilerPreparation::class)) {
            $compilerPreparation = new CompilerPreparation(
                $this->serviceManager,
                new Console\Input\ArgvInput(),
                new File()
            );

            $compilerPreparation->handleCompilerEnvironment();
        }
    }

    /**
     * Retrieves vendor commands.
     *
     * @param ObjectManagerInterface $objectManager the object manager
     *
     * @return array an array with external commands
     */
    protected function getVendorCommands($objectManager)
    {
        $commands = [];
        $this->failedCommands = []; // Reset for each call

        foreach (CommandLocator::getCommands() as $commandListClass) {
            if (!class_exists($commandListClass)) {
                continue;
            }

            try {
                $commandList = $objectManager->create($commandListClass);
                $commands[] = $commandList->getCommands();
            } catch (\Exception $e) {
                // Store failure information
                $this->failedCommands[] = [
                    'class' => $commandListClass,
                    'error' => $e->getMessage(),
                    'type' => get_class($e),
                    'file' => $e->getFile() . ':' . $e->getLine()
                ];

                // Log the error
                $errorMessage = sprintf(
                    'Failed to load command class %s: %s',
                    $commandListClass,
                    $e->getMessage()
                );
                $this->logger->error($errorMessage, [
                    'exception' => $e,
                    'trace' => $e->getTraceAsString()
                ]);

                // Developer mode: fail immediately with clear exception
                if ($this->isDeveloperMode()) {
                    throw $e;
                }

                // Production mode: continue loading other commands
            }
        }

        // Log summary in production mode if there were failures
        if (!empty($this->failedCommands) && !$this->isDeveloperMode()) {
            $this->logFailedCommandsSummary();
        }

        return array_merge([], ...$commands);
    }

    /**
     * Generate and return the Command Loader
     *
     * @throws \LogicException
     * @throws \BadMethodCallException
     */
    private function getCommandLoader(): Console\CommandLoader\CommandLoaderInterface
    {
        $commandLoaders = [];
        if (class_exists(SetupCommandLoader::class)) {
            $commandLoaders[] = new SetupCommandLoader($this->serviceManager);
        }
        $commandLoaders[] = $this->objectManager->create(CommandLoader::class);

        return $this->objectManager->create(Aggregate::class, [
            'commandLoaders' => $commandLoaders
        ]);
    }

    /**
     * Check if application is running in developer mode.
     *
     * @return bool
     */
    private function isDeveloperMode(): bool
    {
        try {
            // Check via env.php MAGE_MODE setting first
            if ($this->objectManager) {
                $deploymentConfig = $this->objectManager->get(DeploymentConfig::class);
                $mode = $deploymentConfig->get(AppState::PARAM_MODE);
                if ($mode && $mode !== AppState::MODE_DEVELOPER) {
                    return false;
                }

                // Also check AppState as fallback
                try {
                    /** @var AppState $appState */
                    $appState = $this->objectManager->get(AppState::class);
                    return $appState->getMode() === AppState::MODE_DEVELOPER;
                } catch (\Exception $e) {
                    // Fallback to deployment config value
                    return $mode === AppState::MODE_DEVELOPER;
                }
            }

            // Default to production (safer)
            return false;
        } catch (\Exception $e) {
            // If we can't determine the mode, assume production (safer option)
            return false;
        }
    }

    /**
     * Log a summary of all failed commands.
     *
     * @return void
     */
    private function logFailedCommandsSummary(): void
    {
        if (empty($this->failedCommands)) {
            return;
        }

        $summary = sprintf(
            "Failed to load %d command class(es). The CLI will continue with available commands:" . PHP_EOL,
            count($this->failedCommands)
        );

        foreach ($this->failedCommands as $failure) {
            $summary .= sprintf(
                "  - %s: %s (%s at %s)" . PHP_EOL,
                $failure['class'],
                $failure['error'],
                $failure['type'],
                $failure['file']
            );
        }

        $this->logger->warning($summary);
    }

    /**
     * Log command loading error to both Magento logs and PHP error log.
     *
     * @param string $errorMessage
     * @param \Exception $exception
     * @return void
     */
    private function logCommandLoadingError(string $errorMessage, \Exception $exception): void
    {
        // Try to log to Magento's log system
        $loggedToMagento = false;
        if ($this->logger) {
            try {
                $this->logger->error($errorMessage, [
                    'exception' => $exception,
                    'trace' => $exception->getTraceAsString()
                ]);
                $loggedToMagento = true;

                // Also log a warning that CLI will continue
                $this->logger->warning(
                    'Some commands failed to load. The CLI will continue with available commands. ' .
                    'Check system.log for details.'
                );
            } catch (\Exception $logException) {
                // Logger failed, will show in terminal
                $loggedToMagento = false;
            }
        }

        // Show actionable error in terminal (production mode)
        $terminalMessage = PHP_EOL . str_repeat('=', 80) . PHP_EOL;
        $terminalMessage .= "  MAGENTO CLI ERROR (Production Mode)" . PHP_EOL;
        $terminalMessage .= str_repeat('=', 80) . PHP_EOL;

        // Extract just the first line of error
        $errorLines = explode(PHP_EOL, $errorMessage);
        $terminalMessage .= $errorLines[0] . PHP_EOL;

        // Check if this is a critical core commands failure
        if (strpos($errorMessage, 'CRITICAL: Core Magento commands') !== false) {
            $terminalMessage .= PHP_EOL
                . " CRITICAL: Commands like cache:flush, deploy:mode:set are UNAVAILABLE!" . PHP_EOL;
            $terminalMessage .=  PHP_EOL . "Try running the following command to see the available commands:" . PHP_EOL;
            $terminalMessage .= "  bin/magento list" . PHP_EOL;
        }

        if ($loggedToMagento) {
            $terminalMessage .= PHP_EOL . " Full details logged to: var/log/system.log" . PHP_EOL;
        } else {
            $terminalMessage .=  PHP_EOL . " Full error details shown above" . PHP_EOL;
        }

        $terminalMessage .= str_repeat('=', 80) . PHP_EOL;

        error_log($terminalMessage);
    }
}
