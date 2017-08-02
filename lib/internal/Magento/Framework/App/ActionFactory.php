<?php
/**
 * Action Factory
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

/**
 * @api
 * @since 2.0.0
 */
class ActionFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create action
     *
     * @param string $actionName
     * @return ActionInterface
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    public function create($actionName)
    {
        if (!is_subclass_of($actionName, \Magento\Framework\App\ActionInterface::class)) {
            throw new \InvalidArgumentException('Invalid action name provided');
        }
        return $this->_objectManager->create($actionName);
    }
}
