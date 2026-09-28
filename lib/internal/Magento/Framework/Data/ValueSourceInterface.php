<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
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
