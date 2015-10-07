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
        return array_merge(parent::getDefaultCommands(), $this->getApplicationCommands());
    }

    /**
     * Gets application commands
     *
     * @return array
     */
    protected function getApplicationCommands()
    {
        $setupCommands   = [];
        $modulesCommands = [];

        $bootstrapParam = new ComplexParameter(self::INPUT_KEY_BOOTSTRAP);
        $params = $bootstrapParam->mergeFromArgv($_SERVER, $_SERVER);
        $params[Bootstrap::PARAM_REQUIRE_MAINTENANCE] = null;
        $bootstrap = Bootstrap::create(BP, $params);
        $objectManager = $bootstrap->getObjectManager();
        $serviceManager = \Zend\Mvc\Application::init(require BP . '/setup/config/application.config.php')
            ->getServiceManager();
        /** @var \Magento\Setup\Model\ObjectManagerProvider $omProvider */
        $omProvider = $serviceManager->get('Magento\Setup\Model\ObjectManagerProvider');
        $omProvider->setObjectManager($objectManager);

        if (class_exists('Magento\Setup\Console\CommandList')) {
            $setupCommandList = new \Magento\Setup\Console\CommandList($serviceManager);
            $setupCommands = $setupCommandList->getCommands();
        }

        if ($objectManager->get('Magento\Framework\App\DeploymentConfig')->isAvailable()) {
            /** @var \Magento\Framework\Console\CommandList $commandList */
            $commandList = $objectManager->create('Magento\Framework\Console\CommandList');
            $modulesCommands = $commandList->getCommands();
        }

        $vendorCommands = $this->getVendorCommands($objectManager);

        $commandsList = array_merge(
            $setupCommands,
            $modulesCommands,
            $vendorCommands
        );

        return $commandsList;
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
