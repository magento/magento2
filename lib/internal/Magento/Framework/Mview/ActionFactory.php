<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\Mview;

/**
 * Class \Magento\Framework\Mview\ActionFactory
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
     * @throws \InvalidArgumentException
     * @return ActionInterface
     * @since 2.0.0
     */
    public function get($className)
    {
        $action = $this->objectManager->get($className);
        if (!$action instanceof ActionInterface) {
            throw new \InvalidArgumentException($className . ' doesn\'t implement \Magento\Framework\Mview\ActionInterface');
        }

        return $action;
    }
}
