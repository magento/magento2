<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Config;

use Magento\Framework\ObjectManager\TMap;

class ValueHandlerPool implements \Magento\Payment\Gateway\Config\ValueHandlerPoolInterface
{
    /**
     * Default handler code
     */
    const DEFAULT_HANDLER = 'default';

    /**
     * @var ValueHandlerInterface[]
     */
    private $handlers;

    /**
     * @param TMap $handlers
     */
    public function __construct(
        TMap $handlers
    ) {
        if (!isset($handlers[self::DEFAULT_HANDLER])) {
            throw new \LogicException('Default handler should be provided.');
        }

        $this->handlers = $handlers;
    }

    /**
     * Retrieves an appropriate configuration value handler
     *
     * @param string $field
     * @return ValueHandlerInterface
     */
    public function get($field)
    {
        return isset ($this->handlers[$field])
            ? $this->handlers[$field]
            : $this->handlers[self::DEFAULT_HANDLER];
    }
}
