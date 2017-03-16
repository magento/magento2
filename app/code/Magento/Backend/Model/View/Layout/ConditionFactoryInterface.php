<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\View\Layout;

/**
 * Interface for condition factories.
 */
interface ConditionFactoryInterface
{
    /**
     * @param string $conditionAttributeValue
     *
     * @return VisibilityConditionInterface
     */
    public function create($conditionAttributeValue);
}
