<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data;

/**
 * Interface ValueSourceInterface
 * @since 2.1.0
 */
interface ValueSourceInterface
{
    /**
     * Get value by name
     *
     * @param string $name
     * @return mixed
     * @since 2.1.0
     */
    public function getValue($name);
}
