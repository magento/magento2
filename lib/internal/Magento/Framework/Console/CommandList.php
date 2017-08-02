<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Console;

/**
 * Class CommandList has a list of commands, which can be extended via DI configuration.
 * @since 2.0.0
 */
class CommandList implements CommandListInterface
{
    /**
     * @var string[]
     * @since 2.0.0
     */
    protected $commands;

    /**
     * Constructor
     *
     * @param array $commands
     * @since 2.0.0
     */
    public function __construct(array $commands = [])
    {
        $this->commands = $commands;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getCommands()
    {
        return $this->commands;
    }
}
