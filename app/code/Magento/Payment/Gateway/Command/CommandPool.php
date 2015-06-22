<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Command;

use Magento\Framework\ObjectManager\TMap;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Framework\Exception\NotFoundException;

class CommandPool implements CommandPoolInterface
{
    /**
     * @var CommandInterface[]
     */
    private $commands;

    /**
     * @param TMap $commands
     */
    public function __construct(
        TMap $commands
    ) {
        $this->commands = $commands;
    }

    /**
     * Retrieves operation
     *
     * @param string $commandCode
     * @return CommandInterface
     * @throws NotFoundException
     */
    public function get($commandCode)
    {
        if (!isset($this->commands[$commandCode])) {
            throw new NotFoundException(__('Command %1 does not exist.', $commandCode));
        }

        return $this->commands[$commandCode];
    }
}
