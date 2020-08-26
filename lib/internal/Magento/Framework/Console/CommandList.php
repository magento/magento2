<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Console;

/**
 * Class CommandList has a list of commands, which can be extended via DI configuration.
 * @api
 */
class CommandList implements CommandListInterface
{
    /**
     * @var string[]
     */
    protected $commands;

    /**
     * CommandList constructor is being used for injecting new Commands
     *
     * Registration of new Commands can be done using `di.xml`:
     *  <type name="Magento\Framework\Console\CommandList">
     *  <arguments>
     *       <argument name="commands" xsi:type="array">
     *           <item name="your-command-name" xsi:type="object">Vendor\Module\Console\Command\YourCommand</item>
     *       </argument>
     *  </arguments>
     *  </type>
     *
     * @param array $commands
     */
    public function __construct(array $commands = [])
    {
        $this->commands = $commands;
    }

    /**
     * @inheritdoc
     */
    public function getCommands(): array
    {
        return $this->commands;
    }
}
