<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Element;

/**
 * Interface which allows to modify visibility behavior of UI components
 */
interface ComponentVisibilityInterface
{
    /**
     * Defines if the component can be shown
     *
     * @return bool
     */
    public function isComponentVisible(): bool;
}
