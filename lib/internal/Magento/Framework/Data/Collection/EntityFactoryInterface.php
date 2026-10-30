<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Data\Collection;

/**
 * Interface \Magento\Framework\Data\Collection\EntityFactoryInterface
 *
 * @api
 */
interface EntityFactoryInterface
{
    /**
     * Create new object instance
     *
     * @param string $type
     * @param array $arguments
     * @return mixed
     */
    public function create($type, array $arguments = []);
}
