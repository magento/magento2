<?php
/**
 * Session storage interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Session;

/**
 * Interface \Magento\Framework\Session\StorageInterface
 *
 * @since 2.0.0
 */
interface StorageInterface
{
    /**
     * Initialize storage data
     *
     * @param array $data
     * @return $this
     * @since 2.0.0
     */
    public function init(array $data);

    /**
     * Get current storage namespace
     *
     * @return string
     * @since 2.0.0
     */
    public function getNamespace();
}
