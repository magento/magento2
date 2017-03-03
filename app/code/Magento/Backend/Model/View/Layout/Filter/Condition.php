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
use Magento\Framework\ObjectManagerInterface;

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
     * @var ObjectManagerInterface
     */
    private $objectManager;


    /**
     * Condition constructor.
     *
     * @param StructureManager $structureManager
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        StructureManager $structureManager,
        ObjectManagerInterface $objectManager
    ) {
        $this->structureManager = $structureManager;
        $this->objectManager = $objectManager;
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
            if (isset($data['attributes']['visibilityCondition']) && $data['attributes']['visibilityCondition']) {
//                $dataProvider = null;
//                if (isset($data['attributes']['visibilityDataProvider'])) {
//                    $dataProvider = $data['attributes']['visibilityDataProvider'];
//                }
                $condition = $this->objectManager->create(
                    $data['attributes']['visibilityCondition']
                );
                if (!$condition->validate()) {
                    $this->structureManager->removeElement($scheduledStructure, $structure, $name);
                }
            }
        }
        return true;
    }
}
