<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Console;

use Magento\Framework\ObjectManagerInterface;

/**
 * Provides list of commands to be available for uninstalled application
 */
class CommandList implements \Magento\Framework\Console\CommandListInterface
{
    /**
     * Object Manager
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager Object Manager
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
    private function getCommandsClasses()
    {
        return [
            \Magento\Deploy\Console\Command\App\ConfigImportCommand::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public function getCommands()
    {
        $commands = [];
        foreach ($this->getCommandsClasses() as $class) {
            if (class_exists($class)) {
                $commands[] = $this->objectManager->get($class);
            } else {
                throw new \Exception('Class ' . $class . ' does not exist');
            }
        }

        return $commands;
    }
}
