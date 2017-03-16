<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\View\Layout\Filter;

use Magento\Backend\Model\View\Layout\AndConditionFactory;
use Magento\Backend\Model\View\Layout\FilterInterface;
use Magento\Backend\Model\View\Layout\StructureManager;
use Magento\Framework\View\Layout\Data\Structure;
use Magento\Framework\View\Layout\ScheduledStructure;

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
     * Returns preconfigured composite condition.
     *
     * @var AndConditionFactory
     */
    private $andConditionFactory;


    /**
     * @param StructureManager $structureManager
     * @param AndConditionFactory $andConditionFactory
     */
    public function __construct(
        StructureManager $structureManager,
        AndConditionFactory $andConditionFactory
    ) {
        $this->structureManager = $structureManager;
        $this->andConditionFactory = $andConditionFactory;
    }

    /**
     * Filter structure according to declared conditions.
     *
     * @param ScheduledStructure $scheduledStructure
     * @param Structure $structure
     * @return bool
     */
    public function filterElement(ScheduledStructure $scheduledStructure, Structure $structure)
    {
        foreach ($scheduledStructure->getElements() as $name => $data) {
            list(, $data) = $data;
            $andCondition = $this->andConditionFactory->create($data['attributes']);
            if (!$andCondition->isVisible($data['attributes'])) {
                $this->structureManager->removeElement($scheduledStructure, $structure, $name);
            }
        }
        return true;
    }
}
