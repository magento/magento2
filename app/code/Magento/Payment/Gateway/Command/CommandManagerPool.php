<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Command;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\ObjectManager\TMap;
use Magento\Framework\ObjectManager\TMapFactory;

/**
 * Class CommandManagerPool
 * @package Magento\Payment\Gateway\Command
 * @api
 */
class CommandManagerPool implements CommandManagerPoolInterface
{
    /**
     * @var CommandManagerInterface[] | TMap
     */
    private $executors;

    /**
     * @param TMapFactory $tmapFactory
     * @param array $executors
     */
    public function __construct(
        TMapFactory $tmapFactory,
        array $executors = []
    ) {
        $this->executors = $tmapFactory->createSharedObjectsMap(
            [
                'array' => $executors,
                'type' => CommandManagerInterface::class
            ]
        );
    }

    /**
     * Returns Command executor for defined payment provider
     *
     * @param string $paymentProviderCode
     * @return CommandManagerInterface
     * @throws NotFoundException
     */
    public function get($paymentProviderCode)
    {
        if (!isset($this->executors[$paymentProviderCode])) {
            throw new NotFoundException(
                __('Command Executor for %1 is not defined.', $paymentProviderCode)
            );
        }

        return $this->executors[$paymentProviderCode];
    }
}
