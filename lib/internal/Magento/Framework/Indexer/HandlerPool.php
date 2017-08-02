<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer;

use Magento\Framework\Indexer\Handler\DefaultHandler;
use Magento\Framework\ObjectManagerInterface;

/**
 * @api Instantiate save handler when implementing custom Indexer\Action
 * @since 2.0.0
 */
class HandlerPool
{
    /**
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * @var HandlerInterface
     * @since 2.0.0
     */
    protected $defaultHandler;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param DefaultHandler $defaultHandler
     * @since 2.0.0
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        DefaultHandler $defaultHandler
    ) {
        $this->defaultHandler = $defaultHandler;
        $this->objectManager = $objectManager;
    }

    /**
     * Get handler class instance
     *
     * @param string $handlerClass
     * @throws \InvalidArgumentException
     * @return HandlerInterface
     * @since 2.0.0
     */
    public function get($handlerClass = null)
    {
        if ($handlerClass === null) {
            return $this->defaultHandler;
        }

        $handler = $this->objectManager->get($handlerClass);
        if (!$handler instanceof HandlerInterface) {
            throw new \InvalidArgumentException(
                $handlerClass . ' doesn\'t implement \Magento\Framework\Indexer\HandlerInterface'
            );
        }

        return $handler;
    }
}
