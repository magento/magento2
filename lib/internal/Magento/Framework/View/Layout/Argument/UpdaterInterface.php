<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Layout\Argument;

/**
 * Interface of value modification with no value loss
 *
 * @api
 */
interface UpdaterInterface
{
    /**
     * Return modified version of an input value
     *
     * @param mixed $value
     * @return mixed
     */
    public function update($value);
}
