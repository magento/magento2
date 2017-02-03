<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Console;

use Magento\Framework\Filesystem\Driver\File;
use Symfony\Component\Console\Application as SymfonyApplication;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\Shell\ComplexParameter;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Magento2 CLI Application. This is the hood for all command line tools supported by Magento.
 *
 * {@inheritdoc}
 */
class Cli extends SymfonyApplication
{
    /**
     * Cli exit codes
     */
    const RETURN_SUCCESS = 0;
    const RETURN_FAILURE = 1;

    /**
     * Name of input option
     */
    const INPUT_KEY_BOOTSTRAP = 'bootstrap';

    /** @var \Zend\ServiceManager\ServiceManager */
    private $serviceManager;

    /**
     * Initialization exception
     *
     * @var \Exception
     */
    private $initException;

    /**
     * Process an error happened during initialization of commands, if any
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    public function doRun(InputInterface $input, OutputInterface $output)
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
     * @param string $name    The name of the application
     * @param string $version The version of the application
     */
    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        $this->serviceManager = \Zend\Mvc\Application::init(require BP . '/setup/config/application.config.php')
            ->getServiceManager();
        /**
         * Temporary workaround until the compiler is able to clear the generation directory. (MAGETWO-44493)
         */
        if (class_exists('Magento\Setup\Console\CompilerPreparation')) {
            (new \Magento\Setup\Console\CompilerPreparation($this->serviceManager, new ArgvInput(), new File()))
                ->handleCompilerEnvironment();
        }

        parent::__construct($name, $version);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultCommands()
    {
        return array_merge(parent::getDefaultCommands(), $this->getApplicationCommands());
    }

    /**
     * Gets application commands
     *
     * @return array
     */
    protected function getApplicationCommands()
    {
        $commands = [];
        try {
            $bootstrapParam = new ComplexParameter(self::INPUT_KEY_BOOTSTRAP);
            $params = $bootstrapParam->mergeFromArgv($_SERVER, $_SERVER);
            $params[Bootstrap::PARAM_REQUIRE_MAINTENANCE] = null;
            $bootstrap = Bootstrap::create(BP, $params);
            $objectManager = $bootstrap->getObjectManager();
            /** @var \Magento\Setup\Model\ObjectManagerProvider $omProvider */
            $omProvider = $this->serviceManager->get('Magento\Setup\Model\ObjectManagerProvider');
            $omProvider->setObjectManager($objectManager);

            if (class_exists('Magento\Setup\Console\CommandList')) {
                $setupCommandList = new \Magento\Setup\Console\CommandList($this->serviceManager);
                $commands = array_merge($commands, $setupCommandList->getCommands());
            }

            if ($objectManager->get('Magento\Framework\App\DeploymentConfig')->isAvailable()) {
                /** @var \Magento\Framework\Console\CommandList $commandList */
                $commandList = $objectManager->create('Magento\Framework\Console\CommandList');
                $commands = array_merge($commands, $commandList->getCommands());
            }

            $commands = array_merge($commands, $this->getVendorCommands($objectManager));
        } catch (\Exception $e) {
            $this->initException = $e;
        }
        return $commands;
    }

    /**
     * Gets vendor commands
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @return array
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
