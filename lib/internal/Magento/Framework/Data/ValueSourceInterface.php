<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Data;

/**
 * Interface ValueSourceInterface
 *
 * @api
 */
interface ValueSourceInterface
{
    /**
     * Get value by name
     *
     * @param string $name
     * @return mixed
     */
    public function getValue($name);
}
