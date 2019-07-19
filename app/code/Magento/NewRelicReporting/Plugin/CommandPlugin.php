<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NewRelicReporting\Plugin;

use Magento\NewRelicReporting\Model\Config;
use Magento\NewRelicReporting\Model\NewRelicWrapper;
use Symfony\Component\Console\Command\Command;

/**
 * Describe NewRelic commands plugin.
 */
class CommandPlugin
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var NewRelicWrapper
     */
    private $newRelicWrapper;

    /**
     * @var string[]
     */
    private $skipCommands;

    /**
     * @param Config $config
     * @param NewRelicWrapper $newRelicWrapper
     * @param array $skipCommands
     */
    public function __construct(
        Config $config,
        NewRelicWrapper $newRelicWrapper,
        array $skipCommands = []
    ) {
        $this->config = $config;
        $this->newRelicWrapper = $newRelicWrapper;
        $this->skipCommands = $skipCommands;
    }

    /**
     * Set NewRelic Transaction name before running command.
     *
     * @param Command $command
     * @param array $args
     * @return array
     */
    public function beforeRun(Command $command, ...$args)
    {
        if (!in_array($command->getName(), $this->skipCommands)) {
            $this->newRelicWrapper->setTransactionName(
                sprintf('CLI %s', $command->getName())
            );
        }

        return $args;
    }
}
