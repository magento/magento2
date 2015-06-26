<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\IndexerInterface;

class SaveHandlerPool
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
     * @param string $saveHandlerClass
     * @throws \InvalidArgumentException
     * @return IndexerInterface
     */
    public function get($saveHandlerClass)
    {
        $handler = $this->objectManager->get($saveHandlerClass);
        if (!$handler instanceof IndexerInterface) {
            throw new \InvalidArgumentException(
                $saveHandlerClass . ' doesn\'t implement \Magento\Framework\IndexerInterface'
            );
        }

        return $handler;
    }
}
