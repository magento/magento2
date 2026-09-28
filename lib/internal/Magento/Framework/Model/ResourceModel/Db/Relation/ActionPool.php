<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Model\ResourceModel\Db\Relation;

use Magento\Framework\ObjectManagerInterface as ObjectManager;

/**
 * Class ActionPool
 */
class ActionPool
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var array
     */
    protected $relationActions;

    /**
     * @param ObjectManager $objectManager
     * @param array $relationActions
     */
    public function __construct(
        ObjectManager $objectManager,
        array $relationActions = []
    ) {
        $this->objectManager = $objectManager;
        $this->relationActions = $relationActions;
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
        if (!isset($this->relationActions[$entityType][$actionName])) {
            return $actions;
        }
        foreach ($this->relationActions[$entityType][$actionName] as $actionClassName) {
            $action = $this->objectManager->get($actionClassName);
            $actions[] = $action;
        }
        return $actions;
    }
}
