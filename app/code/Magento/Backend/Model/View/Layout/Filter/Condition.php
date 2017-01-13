<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
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
