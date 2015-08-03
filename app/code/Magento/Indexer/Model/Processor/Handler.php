<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\Processor;

use Magento\Indexer\Model\HandlerInterface;
use Magento\Indexer\Model\HandlerPool;

class Handler
{
    /**
     * @var HandlerPool
     */
    private $handlerPool;

    /**
     * @param HandlerPool $handlerPool
     */
    public function __construct(HandlerPool $handlerPool)
    {
        $this->handlerPool = $handlerPool;
    }

    /**
     * @param array $handlerNames
     * @return HandlerInterface[]
     */
    public function process(array $handlerNames)
    {
        $handlerObjects = [];
        foreach ($handlerNames as $name => $className) {
            $handlerObjects[$name] = $this->handlerPool->get($className);
        }

        return $handlerObjects;
    }
}
