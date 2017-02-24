<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\View\Layout;

/**
 * Class ConditionInterface
 *
 * Introduces family of visibility conditions for layout elements at the backend.
 * By using this interface a developer can specify dynamic rule for ui component visibility.
 * To be applied a condition should be registered in ConditionPool
 *
 * <type name="Magento\Backend\Model\View\Layout\ConditionPool">
 *     <arguments>
 *         <argument name="conditions" xsi:type="array">
 *             <item name="condition::identifier" xsi:type="string">Condition\Implementation</item>
 *         </argument>
 *     </arguments>
 * </type>
 * 
 * Registered condition can be used by ui component declaration in layout
 *
 * <uiComponent name="form" condition="condition::identifier" />
 *
 * "condition" just another optional attribute of ui component declaration
 */
interface ConditionInterface
{
    /**
     * Validate logical condition for ui component
     * If validation passed block will be displayed
     *
     * @return bool
     */
    public function validate();
}
