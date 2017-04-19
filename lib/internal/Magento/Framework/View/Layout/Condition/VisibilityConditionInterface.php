<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout\Condition;

/**
 * Class VisibilityConditionInterface
 *
 * Introduces family of visibility conditions for layout elements.
 * By using this interface a developer can specify dynamic rule for ui component visibility.
 *
 * Condition can be used by ui component declaration in layout
 *
 * <uiComponent name="form">
 *      <visibilityCondition name='can_show_awesome_element'>
 *          <arguments>
 *              <argument name="aclResource" xsi:type="string">Magento_Framework::awesome_page</argument>
 *              <argument name="extraData" xsi:type="array"></argument>
 *          <arguments>
 *      </visibilityCondition>
 * </uiComponent>
 *
 * "visibilityCondition" just another optional child element of ui component declaration
 */
interface VisibilityConditionInterface
{
    /**
     * Validate logical condition for ui component
     * If validation passed block will be displayed
     *
     * @param array $arguments Attributes from element node.
     *
     * @return bool
     */
    public function isVisible(array $arguments);

    /**
     * @return string
     */
    public function getName();
}
