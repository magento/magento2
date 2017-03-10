<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\View\Layout\Filter;

use Magento\Backend\Model\View\Layout\FilterInterface;
use Magento\Backend\Model\View\Layout\StructureManager;
use Magento\Backend\Model\View\Layout\VisibilityConditionFactory;
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
     * @var VisibilityConditionFactory
     */
    private $visibilityConditionFactory;

    /**
     * @var array The names of attributes which could be used in visibility conditions
     */
    private $conditionAttributes = ['aclResource', 'ifconfig'];


    /**
     * Condition constructor.
     *
     * @param StructureManager $structureManager
     * @param VisibilityConditionFactory $visibilityConditionFactory
     */
    public function __construct(
        StructureManager $structureManager,
        VisibilityConditionFactory $visibilityConditionFactory
    ) {
        $this->structureManager = $structureManager;
        $this->visibilityConditionFactory = $visibilityConditionFactory;
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
                $condition = $this->visibilityConditionFactory->create(
                    $data['attributes']['visibilityCondition']
                );
                $attributes = array_filter(
                    $data['attributes'],
                    function ($key) {
                        return in_array($key, $this->conditionAttributes);
                    },
                    ARRAY_FILTER_USE_KEY
                );
                $attributes['name'] = $name;
                
                if (!$condition->isVisible($attributes)) {
                    $this->structureManager->removeElement($scheduledStructure, $structure, $name);
                }
            }
        }
        return true;
    }
}
