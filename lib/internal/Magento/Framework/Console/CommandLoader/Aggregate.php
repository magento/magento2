<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);


namespace Magento\Framework\Console\CommandLoader;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\Exception\CommandNotFoundException;

/**
 * Class Aggregate has a list of command loaders, which can be extended via DI configuration.
 */
class Aggregate implements CommandLoaderInterface
{
    /** @var CommandLoaderInterface[] */
    private array $commandLoaders;

    /**
     * @param array $commandLoaders
     */
    public function __construct(array $commandLoaders = [])
    {
        $this->commandLoaders = $commandLoaders;
    }

    /**
     * Intiantiate and return the command referred to by $name within the internal command loaders.
     *
     * If $name does not refer to a command, throw a CommandNotFoundException.
     *
     * @param string $name
     * @return Command
     * @throws CommandNotFoundException
     */
    public function get(string $name): Command
    {
        foreach ($this->commandLoaders as $commandLoader) {
            if ($commandLoader->has($name)) {
                return $commandLoader->get($name);
            }
        }

        throw new CommandNotFoundException(sprintf('Command "%s" does not exist.', $name));
    }

    /**
     * Return whether $name refers to a command within the internal command loaders.
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        foreach ($this->commandLoaders as $commandLoader) {
            if ($commandLoader->has($name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return an array of all the command names provided by the internal command loaders.
     *
     * @return string[]
     */
    public function getNames(): array
    {
        return array_merge([], ...array_map(static function (CommandLoaderInterface $commandLoader) {
            return $commandLoader->getNames();
        }, $this->commandLoaders));
    }
}
