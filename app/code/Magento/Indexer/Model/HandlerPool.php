<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model;

use Magento\Framework\ObjectManagerInterface;

class HandlerPool
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Get handler class instance
     *
     * @param string $handlerClass
     * @throws \InvalidArgumentException
     * @return HandlerInterface
     */
    public function get($handlerClass)
    {
        $handler = $this->objectManager->get($handlerClass);
        if (!$handler instanceof HandlerInterface) {
            throw new \InvalidArgumentException(
                $handlerClass . ' doesn\'t implement \Magento\Indexer\Model\HandlerInterface'
            );
        }

        return $handler;
    }
}
