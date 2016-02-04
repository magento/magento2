<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\ResourceModel\Db;

use Magento\Framework\ObjectManagerInterface as ObjectManager;

/**
 * Class ExtensionPool
 */
class ExtensionPool
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var object[]
     */
    protected $extensionActions;

    /**
     * @param ObjectManager $objectManager
     * @param array $extensionActions
     */
    public function __construct(
        ObjectManager $objectManager,
        array $extensionActions = []
    ) {
        $this->objectManager = $objectManager;
        $this->extensionActions = $extensionActions;
    }

    /**
     * @param string $entityType
     * @param string $actionName
     * @return object[]
     * @throws \Exception
     */
    public function getActions($entityType, $actionName)
    {
        $actions = [];
        foreach ($this->extensionActions as $name => $actionGroup) {
            if (isset($actionGroup[$entityType][$actionName])) {
                $actions[$name] = $this->objectManager->get($actionGroup[$entityType][$actionName]);
            } elseif (isset($actionGroup['default'][$actionName])) {
                $actions[$name] = $this->objectManager->get($actionGroup['default'][$actionName]);
            }
        }
        return $actions;
    }
}
