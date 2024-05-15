<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Console;

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ProductMetadata;
use Magento\Framework\Composer\ComposerJsonFinder;
use Magento\Framework\Console\Exception\GenerationDirectoryAccessException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Shell\ComplexParameter;
use Magento\Setup\Application;
use Magento\Setup\Console\CompilerPreparation;
use Magento\Setup\Model\ObjectManagerProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console;
use Magento\Framework\Config\ConfigOptionsListConstants;

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
     * @var $serviceManager
     */
    private $serviceManager;

    /**
     * Initialization exception.
     *
     * @var \Exception
     */
    private $initException;

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
    }

    /**
     * @inheritdoc
     *
     * @throws \Exception The exception in case of unexpected error
     */
    public function doRun(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
    {
        $exitCode = null;
        try {
            $exitCode = parent::doRun($input, $output);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage() . PHP_EOL . $e->getTraceAsString();
            $this->logger->error($errorMessage);
            $this->initException = $e;
        }

        if ($this->initException) {
            throw $this->initException;
        }

        return $exitCode;
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
        try {
            if (class_exists(\Magento\Setup\Console\CommandList::class)) {
                $setupCommandList = new \Magento\Setup\Console\CommandList($this->serviceManager);
                $commands = array_merge($commands, $setupCommandList->getCommands());
            }

            if ($this->objectManager->get(DeploymentConfig::class)->isAvailable()) {
                /** @var CommandListInterface */
                $commandList = $this->objectManager->create(CommandListInterface::class);
                $commands = array_merge($commands, $commandList->getCommands());
            }

            $commands = array_merge(
                $commands,
                $this->getVendorCommands($this->objectManager)
            );
        } catch (\Exception $e) {
            $this->initException = $e;
        }

        return $commands;
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
        $appBootstrapKey = Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS;

        if (isset($requestParams[$appBootstrapKey]) && !isset($params[$appBootstrapKey])) {
            $params[$appBootstrapKey] = $requestParams[$appBootstrapKey];
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
        foreach (CommandLocator::getCommands() as $commandListClass) {
            if (class_exists($commandListClass)) {
                $commands[] = $objectManager->create($commandListClass)->getCommands();
            }
        }

        return array_merge([], ...$commands);
    }
}
