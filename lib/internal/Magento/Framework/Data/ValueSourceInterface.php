<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data;

/**
 * Interface ValueSourceInterface
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
