<?php
/**
 * Session storage interface
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Session;

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
