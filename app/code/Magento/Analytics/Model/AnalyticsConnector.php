<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model;

use Magento\Analytics\Model\AnalyticsConnector\AnalyticsCommandInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class AnalyticsConnector
 */
class AnalyticsConnector
{
    /** string[] */
    private $commands;

    /** ObjectManagerInterface */
    private $objectManager;

    /**
     * AnalyticsConnector constructor.
     * @param string[] $commands
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
     * Create the instance of the command and execute it.
     * @param string $commandName
     * @return bool
     * @throws NotFoundException
     */
    public function execute($commandName)
    {
        if (!array_key_exists($commandName, $this->commands)) {
            throw new NotFoundException(__('Command was not found.'));
        }
        $command = $this->objectManager->create($this->commands[$commandName]);
        return $command->execute();
    }
}
