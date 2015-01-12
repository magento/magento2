<?php
/**
 * Action Factory
 *
 * Copyright © 2015 Magento. All rights reserved.
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
     * @param array $arguments
     * @return ActionInterface
     * @throws \InvalidArgumentException
     */
    public function create($actionName, array $arguments = [])
    {
        if (!is_subclass_of($actionName, '\Magento\Framework\App\ActionInterface')) {
            throw new \InvalidArgumentException('Invalid action name provided');
        }
        $context = $this->_objectManager->create('Magento\Framework\App\Action\Context', $arguments);
        $arguments['context'] = $context;
        return $this->_objectManager->create($actionName, $arguments);
    }
}
