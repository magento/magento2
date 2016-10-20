<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Mview;

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
     * @throws \InvalidArgumentException
     * @return ActionInterface
     */
    public function get($className)
    {
        $action = $this->objectManager->get($className);
        if (!$action instanceof ActionInterface) {
            throw new \InvalidArgumentException(
                $className . ' doesn\'t implement \Magento\Framework\Mview\ActionInterface'
            );
        }

        return $action;
    }
}
