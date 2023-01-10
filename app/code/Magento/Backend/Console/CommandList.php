<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Console;

use Magento\Backend\Console\Command\MaintenanceAllowIpsCommand;
use Magento\Backend\Console\Command\MaintenanceDisableCommand;
use Magento\Backend\Console\Command\MaintenanceEnableCommand;
use Magento\Backend\Console\Command\MaintenanceStatusCommand;
use Magento\Framework\Console\CommandListInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Provides list of commands to be available for uninstalled application
 */
class CommandList implements CommandListInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Gets list of command classes
     *
     * @return string[]
     */
    private function getCommandsClasses(): array
    {
        return [
            MaintenanceAllowIpsCommand::class,
            MaintenanceDisableCommand::class,
            MaintenanceEnableCommand::class,
            MaintenanceStatusCommand::class
        ];
    }

    /**
     * @inheritdoc
     */
    public function getCommands(): array
    {
        $commands = [];
        foreach ($this->getCommandsClasses() as $class) {
            if (class_exists($class)) {
                $commands[] = $this->objectManager->get($class);
            } else {
                throw new \RuntimeException('Class ' . $class . ' does not exist');
            }
        }

        return $commands;
    }
}
