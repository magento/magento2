<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console;

use Zend\ServiceManager\ServiceManager;

/**
 * Class CommandList contains predefined list of commands for Setup
 *
 * @package Magento\Setup\Console
 */
class CommandList
{
    /**
     * Service Manager
     *
     * @var ServiceManager
     */
    private $serviceManager;

    /**
     * Constructor
     *
     * @param ServiceManager $serviceManager
     */
    public function __construct(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    /**
     * Gets list of setup command classes
     *
     * @return string[]
     */
    protected function getCommandsClasses()
    {
        return [
            'Magento\Setup\Console\Command\ConfigSetCommand',
        ];
    }

    /**
     * Gets list of command instances
     *
     * @return \Symfony\Component\Console\Command\Command[]
     * @throws \Exception
     */
    public function getCommands()
    {
        $commands = [];

        foreach ($this->getCommandsClasses() as $class) {
            if (class_exists($class)) {
                $commands[] = $this->serviceManager->create($class);
            } else {
                throw new \Exception('Class ' . $class . ' does not exist');
            }
        }

        return $commands;
    }
}
