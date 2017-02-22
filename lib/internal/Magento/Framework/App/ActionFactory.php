<?php
/**
 * Action Factory
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

class ActionFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
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
     */
    public function create($actionName)
    {
        if (!is_subclass_of($actionName, '\Magento\Framework\App\ActionInterface')) {
            throw new \InvalidArgumentException('Invalid action name provided');
        }
        return $this->_objectManager->create($actionName);
    }
}
