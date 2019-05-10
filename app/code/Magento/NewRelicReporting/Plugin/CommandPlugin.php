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
     * @param Config $config
     * @param NewRelicWrapper $newRelicWrapper
     */
    public function __construct(
        Config $config,
        NewRelicWrapper $newRelicWrapper
    ) {
        $this->config = $config;
        $this->newRelicWrapper = $newRelicWrapper;
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
        $this->newRelicWrapper->setTransactionName(
            sprintf('CLI %s', $command->getName())
        );

        return $args;
    }
}
