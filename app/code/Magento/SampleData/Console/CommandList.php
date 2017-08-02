<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SampleData\Console;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class CommandList
 * @since 2.0.0
 */
class CommandList implements \Magento\Framework\Console\CommandListInterface
{
    /**
     * Object Manager
     *
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Gets list of command classes
     *
     * @return string[]
     * @since 2.0.0
     */
    protected function getCommandsClasses()
    {
        return [
            \Magento\SampleData\Console\Command\SampleDataDeployCommand::class,
            \Magento\SampleData\Console\Command\SampleDataRemoveCommand::class
        ];
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
