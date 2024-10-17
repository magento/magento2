<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SampleData\Console;

use Exception;
use Magento\Framework\Console\CommandListInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\SampleData\Console\Command\SampleDataDeployCommand;
use Magento\SampleData\Console\Command\SampleDataRemoveCommand;

/**
 * Class CommandList
 */
class CommandList implements CommandListInterface
{
    /**
     * @param ObjectManagerInterface $objectManager Object Manager
     */
    public function __construct(
        private readonly ObjectManagerInterface $objectManager
    ) {
    }

    /**
     * Gets list of command classes
     *
     * @return string[]
     */
    protected function getCommandsClasses()
    {
        return [
            SampleDataDeployCommand::class,
            SampleDataRemoveCommand::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getCommands()
    {
        $commands = [];
        foreach ($this->getCommandsClasses() as $class) {
            if (class_exists($class)) {
                $commands[] = $this->objectManager->get($class);
            } else {
                throw new Exception('Class ' . $class . ' does not exist');
            }
        }
        return $commands;
    }
}
