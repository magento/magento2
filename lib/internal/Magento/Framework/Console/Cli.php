<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Console;

use Symfony\Component\Console\Application as SymfonyApplication;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\Shell\ComplexParameter;

/**
 * Magento2 CLI Application. This is the hood for all command line tools supported by Magento.
 *
 * {@inheritdoc}
 */
class Cli extends SymfonyApplication
{
    /**
     * Name of input option
     */
    const INPUT_KEY_BOOTSTRAP = 'bootstrap';

    /**
     * {@inheritdoc}
     */
    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        foreach ($this->getApplicationCommands() as $command) {
            $commands[] = $this->add($command);
        }

        return $commands;
    }

    /**
     * Gets application commands
     *
     * @return array
     */
    protected function getApplicationCommands()
    {
        $setupCommands   = [];
        $toolsCommands   = [];
        $modulesCommands = [];

        $bootstrapParam = new ComplexParameter(self::INPUT_KEY_BOOTSTRAP);
        $params = $bootstrapParam->mergeFromArgv($_SERVER, $_SERVER);
        $params[Bootstrap::PARAM_REQUIRE_MAINTENANCE] = null;
        $bootstrap = Bootstrap::create(BP, $params);
        $objectManager = $bootstrap->getObjectManager();

        if (class_exists('Magento\Setup\Console\CommandList')) {
            $serviceManager = \Zend\Mvc\Application::init(require BP . '/setup/config/application.config.php')
                ->getServiceManager();
            $setupCommandList = new \Magento\Setup\Console\CommandList($serviceManager);
            $setupCommands = $setupCommandList->getCommands();
        }

        if (class_exists('Magento\Tools\Console\CommandList')) {
            $toolsCommandList = new \Magento\Tools\Console\CommandList();
            $toolsCommands = $toolsCommandList->getCommands();
        }

        if ($objectManager->get('Magento\Framework\App\DeploymentConfig')->isAvailable()) {
            $commandList = $objectManager->create('Magento\Framework\Console\CommandList');
            $modulesCommands = $commandList->getCommands();
        }

        $commandsList = array_merge(
            $setupCommands,
            $toolsCommands,
            $modulesCommands
        );

        return $commandsList;
    }
}
