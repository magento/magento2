<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager\Operation;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class AttributePool
 * @since 2.1.0
 */
class AttributePool
{
    /**
     * @var ObjectManagerInterface
     * @since 2.1.0
     */
    private $objectManager;

    /**
     * @var object[]
     * @since 2.1.0
     */
    private $actions;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $extensionActions
     * @since 2.1.0
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
     * @return object[]
     * @throws \Exception
     * @since 2.1.0
     */
    public function getActions($entityType, $actionName)
    {
        $actions = [];
        foreach ($this->actions as $name => $actionGroup) {
            if (isset($actionGroup[$entityType][$actionName])) {
                $actions[$name] = $this->objectManager->get($actionGroup[$entityType][$actionName]);
            } elseif (isset($actionGroup['default'][$actionName])) {
                $actions[$name] = $this->objectManager->get($actionGroup['default'][$actionName]);
            }
        }
        return $actions;
    }
}
