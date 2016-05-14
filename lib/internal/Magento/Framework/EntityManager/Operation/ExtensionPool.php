<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager\Operation;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class ExtensionPool
 */
class ExtensionPool
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var object[]
     */
    protected $actions;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $extensionActions
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $extensionActions = []
    ) {
        $this->objectManager = $objectManager;
        $this->actions = $extensionActions;
    }

    /**
     * @param string $entityType
     * @param string $actionName
     * @return ExtensionInterface[]
     * @throws \Exception
     */
    public function getActions($entityType, $actionName)
    {
        $actions = [];
        if (!isset($this->actions[$entityType][$actionName])) {
            return $actions;
        }
        foreach ($this->actions[$entityType][$actionName] as $actionClassName) {
            $action = $this->objectManager->get($actionClassName);
            if (!($action instanceof ExtensionInterface)) {
                throw new \LogicException(get_class($action) . ' must implement ' . ExtensionInterface::class);
            }
            $actions[] = $action;
        }
        return $actions;
    }
}
