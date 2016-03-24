<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Console;

/**
 * Class CommandList has a list of commands, which can be extended via DI configuration.
 */
class CommandList implements CommandListInterface
{
    /**
     * @var string[]
     */
    protected $commands;

    /**
     * Constructor
     *
     * @param array $commands
     */
    public function __construct(array $commands = [])
    {
        $this->commands = $commands;
    }

    /**
     * {@inheritdoc}
     */
    public function getCommands()
    {
        return $this->commands;
    }
}
