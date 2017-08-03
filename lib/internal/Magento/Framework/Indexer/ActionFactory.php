<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer;

/**
 * Class \Magento\Framework\Indexer\ActionFactory
 *
 * @since 2.0.0
 */
class ActionFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
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
     * @since 2.0.0
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
