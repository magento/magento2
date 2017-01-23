<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\View\Layout\Filter;

use Magento\Backend\Model\View\Layout\FilterInterface;
use Magento\Backend\Model\View\Layout\StructureManager;
use Magento\Framework\View\Layout\Data\Structure;
use Magento\Framework\View\Layout\ScheduledStructure;
use Magento\Backend\Model\View\Layout\ConditionPool;

/**
 * Class Condition
 *
 * Filter layout structure according to declared visibility conditions
 * Removes elements from structure if condition validation returns false
 * @see how to create and register visibility condition in \Magento\Backend\Model\View\Layout\ConditionInterface
 */
class Condition implements FilterInterface
{
    /**
     * @var StructureManager
     */
    private $structureManager;

    /**
     * @var ConditionPool
     */
    private $conditionPool;

    /**
     * Condition constructor.
     *
     * @param StructureManager $structureManager
     * @param ConditionPool $conditionPool
     */
    public function __construct(
        StructureManager $structureManager,
        ConditionPool $conditionPool
    ) {
        $this->structureManager = $structureManager;
        $this->conditionPool = $conditionPool;
    }

    /**
     * Filter structure according to declared conditions
     *
     * @param ScheduledStructure $scheduledStructure
     * @param Structure $structure
     * @return bool
     */
    public function filterElement(ScheduledStructure $scheduledStructure, Structure $structure)
    {
        foreach ($scheduledStructure->getElements() as $name => $data) {
            list(, $data) = $data;
            if (isset($data['attributes']['condition']) && $data['attributes']['condition']) {
                $condition = $this->conditionPool->getCondition($data['attributes']['condition']);
                if (!$condition->validate()) {
                    $this->structureManager->removeElement($scheduledStructure, $structure, $name);
                }
            }
        }
        return true;
    }
}
