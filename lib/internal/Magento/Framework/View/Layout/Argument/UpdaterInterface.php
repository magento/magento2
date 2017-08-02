<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout\Argument;

/**
 * Interface of value modification with no value loss
 * @since 2.0.0
 */
interface UpdaterInterface
{
    /**
     * Return modified version of an input value
     *
     * @param mixed $value
     * @return mixed
     * @since 2.0.0
     */
    public function update($value);
}
