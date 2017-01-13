<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Model\View\Layout;

use Magento\Framework\View\Layout\ScheduledStructure;
use Magento\Framework\View\Layout\Data\Structure;

/**
 * Class StructureManager
 */
class StructureManager
{
    /**
     * Remove scheduled element
     *
     * @param ScheduledStructure $scheduledStructure
     * @param Structure $structure
     * @param string $elementName
     * @param bool $isChild
     * @return bool
     */
    public function removeElement(
        ScheduledStructure $scheduledStructure,
        Structure $structure,
        $elementName,
        $isChild = false
    ) {
        $elementsToRemove = array_keys($structure->getChildren($elementName));
        $scheduledStructure->unsetElement($elementName);
        foreach ($elementsToRemove as $element) {
            $this->removeElement($scheduledStructure, $structure, $element, true);
        }
        if (!$isChild) {
            $structure->unsetElement($elementName);
        }
        return true;
    }
}
