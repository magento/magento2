<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Config;

use Magento\Framework\ObjectManager\TMap;
use Magento\Framework\ObjectManager\TMapFactory;

/**
 * Default implementation of config value handlers pool.
 * This class designed to be base for virtual types.
 * Direct injection of this class is not recommended (inject ValueHandlerPoolInterface instead).
 * Inheritance from this class is not recommended (declare virtual type or implement ValueHandlerPoolInterface instead).
 *
 * @api
 */
class ValueHandlerPool implements \Magento\Payment\Gateway\Config\ValueHandlerPoolInterface
{
    /**
     * Default handler code
     */
    const DEFAULT_HANDLER = 'default';

    /**
     * @var ValueHandlerInterface[] | TMap
     */
    private $handlers;

    /**
     * @param TMapFactory $tmapFactory
     * @param array $handlers
     */
    public function __construct(
        TMapFactory $tmapFactory,
        array $handlers
    ) {
        if (!isset($handlers[self::DEFAULT_HANDLER])) {
            throw new \LogicException('Default handler should be provided.');
        }

        $this->handlers = $tmapFactory->create(
            [
                'array' => $handlers,
                'type' => ValueHandlerInterface::class
            ]
        );
    }

    /**
     * Retrieves an appropriate configuration value handler
     *
     * @param string $field
     * @return ValueHandlerInterface
     */
    public function get($field)
    {
        return isset($this->handlers[$field])
            ? $this->handlers[$field]
            : $this->handlers[self::DEFAULT_HANDLER];
    }
}
