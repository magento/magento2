<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);


namespace Magento\Framework\Console;

use Magento\Framework\ObjectManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\Exception\CommandNotFoundException;

/**
 * Class CommandLoader allows for deferred initialization of Symfony Commands
 */
class CommandLoader implements CommandLoaderInterface
{
    /**
     * List of commands in the format [ 'command:name' => 'Fully\Qualified\ClassName' ]
     * @var array
     */
    private array $commands;

    /** @var ObjectManagerInterface */
    private ObjectManagerInterface $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $commands
     */
    public function __construct(ObjectManagerInterface $objectManager, array $commands = [])
    {
        $this->objectManager = $objectManager;
        $this->commands = array_combine(array_column($commands, 'name'), array_column($commands, 'class'));
    }

    /**
     * Using the ObjectManager, instantiate the requested command.
     *
     * If the command name is not configured, throw a CommandNotFoundException.
     *
     * @param string $name
     * @return Command
     * @throws CommandNotFoundException
     */
    public function get(string $name): Command
    {
        if ($this->has($name)) {
            return $this->objectManager->create($this->commands[$name]);
        }
        throw new CommandNotFoundException(sprintf('Command "%s" does not exist.', $name));
    }

    /**
     * Return whether the requested $name is present in the commands array
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->commands[$name]);
    }

    /**
     * Return an array of the available command names
     *
     * @return string[]
     */
    public function getNames(): array
    {
        return array_keys($this->commands);
    }
}
