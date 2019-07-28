<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Gateway\Command;

use Magento\Framework\ObjectManager\TMap;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\ObjectManager\TMapFactory;

/**
 * Class CommandPool
 * @api
 * @since 100.0.2
 */
class CommandPool implements CommandPoolInterface
{
    /**
     * @var CommandInterface[] | TMap
     */
    private $commands;

    /**
     * @param TMapFactory $tmapFactory
     * @param array $commands
     */
    public function __construct(
        TMapFactory $tmapFactory,
        array $commands = []
    ) {
        $this->commands = $tmapFactory->create(
            [
                'array' => $commands,
                'type' => CommandInterface::class
            ]
        );
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
            throw new NotFoundException(
                __('The "%1" command doesn\'t exist. Verify the command and try again.', $commandCode)
            );
        }

        return $this->commands[$commandCode];
    }
}
