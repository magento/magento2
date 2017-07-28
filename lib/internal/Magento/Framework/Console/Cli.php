<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Console;

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ProductMetadata;
use Magento\Framework\App\State;
use Magento\Framework\Composer\ComposerJsonFinder;
use Magento\Framework\Console\Exception\GenerationDirectoryAccessException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Shell\ComplexParameter;
use Magento\Setup\Application;
use Magento\Setup\Console\CompilerPreparation;
use Magento\Setup\Model\ObjectManagerProvider;
use Symfony\Component\Console;
use Zend\ServiceManager\ServiceManager;

/**
 * Magento 2 CLI Application.
 * This is the hood for all command line tools supported by Magento.
 *
 * {@inheritdoc}
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
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
     * @since 2.0.0
     */
    private $initException;

    /**
     * Object Manager.
     *
     * @var ObjectManagerInterface
     * @since 2.2.0
     */
    private $objectManager;

    /**
     * @param string $name the application name
     * @param string $version the application version
     * @SuppressWarnings(PHPMD.ExitExpression)
     * @since 2.0.0
     */
    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        try {
            $configuration = require BP . '/setup/config/application.config.php';
            $bootstrapApplication = new Application();
            $application = $bootstrapApplication->bootstrap($configuration);
            $this->serviceManager = $application->getServiceManager();

            $this->assertCompilerPreparation();
            $this->initObjectManager();
            $this->assertGenerationPermissions();
        } catch (\Exception $exception) {
            $output = new \Symfony\Component\Console\Output\ConsoleOutput();
            $output->writeln(
                '<error>' . $exception->getMessage() . '</error>'
            );

            exit(static::RETURN_FAILURE);
        }

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
     * @throws \Exception The exception in case of unexpected error
     * @since 2.0.0
     */
    public function doRun(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
    {
        $exitCode = parent::doRun($input, $output);

        if ($this->initException) {
            throw $this->initException;
        }

        return $exitCode;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function getDefaultCommands()
    {
        return array_merge(parent::getDefaultCommands(), $this->getApplicationCommands());
    }

    /**
     * Gets application commands.
     *
     * @return array a list of available application commands
     * @since 2.0.0
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
     * @since 2.2.0
     */
    private function initObjectManager()
    {
        $params = (new ComplexParameter(self::INPUT_KEY_BOOTSTRAP))->mergeFromArgv($_SERVER, $_SERVER);
        $params[Bootstrap::PARAM_REQUIRE_MAINTENANCE] = null;

        $this->objectManager = Bootstrap::create(BP, $params)->getObjectManager();

        /** @var ObjectManagerProvider $omProvider */
        $omProvider = $this->serviceManager->get(ObjectManagerProvider::class);
        $omProvider->setObjectManager($this->objectManager);
    }

    /**
     * Checks whether generation directory is read-only.
     * Depends on the current mode:
     *      production - application will proceed
     *      default - application will be terminated
     *      developer - application will be terminated
     *
     * @return void
     * @throws GenerationDirectoryAccessException If generation directory is read-only in developer mode
     * @since 2.2.0
     */
    private function assertGenerationPermissions()
    {
        /** @var GenerationDirectoryAccess $generationDirectoryAccess */
        $generationDirectoryAccess = $this->objectManager->create(
            GenerationDirectoryAccess::class,
            ['serviceManager' => $this->serviceManager]
        );
        /** @var State $state */
        $state = $this->objectManager->get(State::class);

        if ($state->getMode() !== State::MODE_PRODUCTION
            && !$generationDirectoryAccess->check()
        ) {
            throw new GenerationDirectoryAccessException();
        }
    }

    /**
     * Checks whether compiler is being prepared.
     *
     * @return void
     * @throws GenerationDirectoryAccessException If generation directory is read-only
     * @since 2.2.0
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
     * @since 2.0.0
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
