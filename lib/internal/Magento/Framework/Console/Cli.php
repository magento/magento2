<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Console;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;
use Magento\Framework\Code\Generator\Io;
use Magento\Framework\Composer\ComposerJsonFinder;
use Magento\Framework\Exception\FileSystemException;
use Magento\Setup\Model\ObjectManagerProvider;
use Symfony\Component\Console;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Shell\ComplexParameter;
use Magento\Setup\Console\CompilerPreparation;
use Magento\Framework\App\ProductMetadata;
use Magento\Framework\ObjectManagerInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Magento 2 CLI Application.
 * This is the hood for all command line tools supported by Magento.
 *
 * {@inheritdoc}
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Cli extends Console\Application
{
    /**
     * Name of input option.
     */
    const INPUT_KEY_BOOTSTRAP = 'bootstrap';

    /**#@+
     * Cli exit codes.
     */
    const RETURN_SUCCESS = 0;
    const RETURN_FAILURE = 1;
    /**#@-*/

    /**
     * Service Manager.
     *
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
     * Object Manager.
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param string $name the application name
     * @param string $version the application version
     */
    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        $this->serviceManager = \Zend\Mvc\Application::init(require BP . '/setup/config/application.config.php')
            ->getServiceManager();

        $this->assertCompilerPreparation();
        $this->initObjectManager();
        $this->assertGenerationPermissions();

        if ($version == 'UNKNOWN') {
            $directoryList = new DirectoryList(BP);
            $composerJsonFinder = new ComposerJsonFinder($directoryList);
            $productMetadata = new ProductMetadata($composerJsonFinder);
            $version = $productMetadata->getVersion();
        }

        parent::__construct($name, $version);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception the exception in case of unexpected error
     */
    public function doRun(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
    {
        $exitCode = parent::doRun($input, $output);

        if ($this->initException) {
            $output->writeln(
                "<error>We're sorry, an error occurred. Try clearing the cache and code generation directories. "
                . "By default, they are: var/cache, var/di, var/generation, and var/page_cache.</error>"
            );

            throw $this->initException;
        }

        return $exitCode;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultCommands()
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
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    private function initObjectManager()
    {
        try {
            $params = (new ComplexParameter(self::INPUT_KEY_BOOTSTRAP))->mergeFromArgv($_SERVER, $_SERVER);
            $params[Bootstrap::PARAM_REQUIRE_MAINTENANCE] = null;

            $this->objectManager = Bootstrap::create(BP, $params)->getObjectManager();

            /** @var ObjectManagerProvider $omProvider */
            $omProvider = $this->serviceManager->get(ObjectManagerProvider::class);
            $omProvider->setObjectManager($this->objectManager);
        } catch (FileSystemException $exception) {
            $this->writeGenerationDirectoryReadError();

            exit(static::RETURN_FAILURE);
        }
    }

    /**
     * Checks whether generation directory is read-only.
     * Depends on the current mode:
     *      production - application will proceed
     *      default - application will be terminated
     *      developer - application will be terminated
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    private function assertGenerationPermissions()
    {
        /** @var GenerationDirectoryAccess $generationDirectoryAccess */
        $generationDirectoryAccess = $this->objectManager->create(
            GenerationDirectoryAccess::class,
            ['serviceManager' => $this->serviceManager]
        );
        /** @var DeploymentConfig $deploymentConfig */
        $deploymentConfig = $this->objectManager->get(DeploymentConfig::class);
        /** @var State $state */
        $state = $this->objectManager->get(State::class);

        if (
            $deploymentConfig->isAvailable()
            && $state->getMode() !== State::MODE_PRODUCTION
            && !$generationDirectoryAccess->check()
        ) {
            $this->writeGenerationDirectoryReadError();

            exit(static::RETURN_FAILURE);
        }
    }

    /**
     * Checks whether compiler is being prepared.
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExitExpression)
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

            try {
                $compilerPreparation->handleCompilerEnvironment();
            } catch (FileSystemException $e) {
                $this->writeGenerationDirectoryReadError();

                exit(static::RETURN_FAILURE);
            }
        }
    }

    /**
     * Writes read error to console.
     *
     * @return void
     */
    private function writeGenerationDirectoryReadError()
    {
        $output = new Console\Output\ConsoleOutput();
        $output->writeln(
            '<error>'
            . 'Command line user does not have read and write permissions on ' . Io::DEFAULT_DIRECTORY . ' directory. '
            . 'Please address this issue before using Magento command line.'
            . '</error>'
        );
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
                $commands = array_merge(
                    $commands,
                    $objectManager->create($commandListClass)->getCommands()
                );
            }
        }

        return $commands;
    }
}
