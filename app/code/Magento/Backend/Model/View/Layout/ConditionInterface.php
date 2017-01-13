<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\View\Layout;

/**
 * Class ConditionInterface
 */
interface ConditionInterface
{
    /**
     * Validate logical condition for block
     *
     * @return bool
     */
    public function validate();
}
