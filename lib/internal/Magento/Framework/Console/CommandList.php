<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Console;

use Magento\Framework\ObjectManagerInterface;

class CommandList
{
    /**
     * @var string[]
     */
    protected $commands;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param array $commands
     */
    public function __construct(ObjectManagerInterface $objectManager, array $commands = [])
    {
        $this->objectManager = $objectManager;
        $this->commands = $commands;
    }

    /**
     * Gets list of command instances
     *
     * @return \Symfony\Component\Console\Command\Command[]
     */
    public function getCommands()
    {
        $commands = [];
        foreach ($this->commands as $class) {
            if (class_exists($class)) {
                $commands[] = $this->objectManager->get($class);
            }
        }

        return $commands;
    }

}
