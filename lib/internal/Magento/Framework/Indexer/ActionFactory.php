<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer;

use Magento\Framework\Indexer\ActionInterface;

class ActionFactory
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
     * Get action class instance by class name
     *
     * @param string $className
     * @param [] $arguments
     * @throws \InvalidArgumentException
     * @return ActionInterface
     */
    public function create($className, $arguments = [])
    {
        $action = $this->objectManager->create($className, $arguments);
        if (!$action instanceof ActionInterface) {
            throw new \InvalidArgumentException(
                $className . ' doesn\'t implement \Magento\Framework\Indexer\ActionInterface'
            );
        }

        return $action;
    }
}
