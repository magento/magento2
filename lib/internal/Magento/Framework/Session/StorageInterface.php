<?php
/**
 * Session storage interface
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Session;

/**
 * Interface \Magento\Framework\Session\StorageInterface
 *
 * @api
 */
interface StorageInterface
{
    /**
     * Initialize storage data
     *
     * @param array $data
     * @return $this
     */
    public function init(array $data);

    /**
     * Get current storage namespace
     *
     * @return string
     */
    public function getNamespace();
}
