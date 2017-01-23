<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Model\View\Layout;

use Magento\Framework\View\Layout\ScheduledStructure;
use Magento\Framework\View\Layout\Data\Structure;

/**
 * Interface FilterInterface
 *
 * Introduces family of layout structure modifiers. By using this modifiers Magento limits visibility
 * for layout entities at the backend. (Example: ACL filter)
 * To be applied filter should be passed as an argument into \Magento\Backend\Model\View\Layout\Filter
 *
 * <type name="Magento\Backend\Model\View\Layout\Filter">
 *     <arguments>
 *         <argument name="filters" xsi:type="array">
 *             <item name="acl-filter" xsi:type="object">Magento\Backend\Model\View\Layout\Filter\Acl</item>
 *         </argument>
 *     </arguments>
 * </type>
 * 
 */
interface FilterInterface
{
    /**
     * Filter structure element
     *
     * @param ScheduledStructure $scheduledStructure
     * @param Structure $structure
     * @return bool
     */
    public function filterElement(ScheduledStructure $scheduledStructure, Structure $structure);
}
