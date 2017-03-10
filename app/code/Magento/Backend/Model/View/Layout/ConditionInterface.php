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
 *
 * Condition can be used by ui component declaration in layout
 *
 * <uiComponent name="form" visibilityCondition="ConditionFullClassPath" />
 *
 * "visibilityCondition" just another optional attribute of ui component declaration
 */
interface ConditionInterface
{
    /**
     * Validate logical condition for ui component
     * If validation passed block will be displayed
     *
     * @param string $elementName
     * @param array $arguments
     *
     * @return bool
     */
    public function validate($elementName, array $arguments);
}
