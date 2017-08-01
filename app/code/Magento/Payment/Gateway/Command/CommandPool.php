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
 * @since 2.0.0
 */
class CommandPool implements CommandPoolInterface
{
    /**
     * @var CommandInterface[] | TMap
     * @since 2.0.0
     */
    private $commands;

    /**
     * @param TMapFactory $tmapFactory
     * @param array $commands
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function get($commandCode)
    {
        if (!isset($this->commands[$commandCode])) {
            throw new NotFoundException(__('Command %1 does not exist.', $commandCode));
        }

        return $this->commands[$commandCode];
    }
}
