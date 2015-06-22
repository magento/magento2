<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model;

class SaveHandlerPool
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Get handler class instance
     *
     * @param string $saveHandlerClass
     * @throws \InvalidArgumentException
     * @return SaveHandlerInterface
     */
    public function get($saveHandlerClass)
    {
        $handler = $this->objectManager->get($saveHandlerClass);
        if (!$handler instanceof SaveHandlerInterface) {
            throw new \InvalidArgumentException(
                $saveHandlerClass . ' doesn\'t implement \Magento\Indexer\Model\SaveHandlerInterface'
            );
        }

        return $handler;
    }
}
