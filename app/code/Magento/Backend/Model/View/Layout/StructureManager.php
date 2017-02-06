<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Model\View\Layout;

use Magento\Framework\View\Layout\ScheduledStructure;
use Magento\Framework\View\Layout\Data\Structure;

/**
 * Class StructureManager
 *
 * Is responsible for managing layout structure items
 * By using this class developer can remove layout entities (block, uiComponent) from scheduled structure
 * Removed entities will not appear at rendered page
 */
class StructureManager
{
    /**
     * Removes scheduled element from structure by name, also removes child elements
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
