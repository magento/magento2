<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\ObjectManagerInterface;

/**
 * A connector to external services.
 *
 * Aggregates and executes commands which perform requests to external services.
 */
class Connector
{
    /**
     * A list of possible commands.
     *
     * An associative array in format: 'command_name' => 'command_class_name'.
     *
     * The list may be configured in each module via '/etc/di.xml'.
     *
     * @var string[]
     */
    private $commands;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param array $commands
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        array $commands,
        ObjectManagerInterface $objectManager
    ) {
        $this->commands = $commands;
        $this->objectManager = $objectManager;
    }

    /**
     * Executes a command in accordance with the given name.
     *
     * @param string $commandName
     * @return bool
     * @throws NotFoundException if the command is not found.
     */
    public function execute($commandName)
    {
        if (!array_key_exists($commandName, $this->commands)) {
            throw new NotFoundException(__('Command was not found.'));
        }

        /** @var \Magento\Analytics\Model\Connector\CommandInterface $command */
        $command = $this->objectManager->create($this->commands[$commandName]);

        return $command->execute();
    }
}
