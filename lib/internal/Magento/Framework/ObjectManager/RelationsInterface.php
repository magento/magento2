<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager;

/**
 * Interface \Magento\Framework\ObjectManager\RelationsInterface
 *
 * @since 2.0.0
 */
interface RelationsInterface
{
    /**
     * Check whether requested type is available for read
     *
     * @param string $type
     * @return bool
     * @since 2.0.0
     */
    public function has($type);

    /**
     * Retrieve list of parents
     *
     * @param string $type
     * @return array
     * @since 2.0.0
     */
    public function getParents($type);
}
