<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\ResourceModel\Db\Relation;

use Magento\Framework\ObjectManagerInterface as ObjectManager;
use Magento\Framework\Model\ResourceModel\Db\ProcessEntityRelationInterface;

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
            //if (!$action instanceof ProcessEntityRelationInterface) {
            //    throw new \Exception('Not compliant with action interface');
            //}
            $actions[] = $action;
        }
        return $actions;
    }
}
