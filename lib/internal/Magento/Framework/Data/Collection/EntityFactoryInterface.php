<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Collection;

/**
 * Interface \Magento\Framework\Data\Collection\EntityFactoryInterface
 *
 * @since 2.0.0
 */
interface EntityFactoryInterface
{
    /**
     * Create new object instance
     *
     * @param string $type
     * @param array $arguments
     * @return mixed
     * @since 2.0.0
     */
    public function create($type, array $arguments = []);
}
