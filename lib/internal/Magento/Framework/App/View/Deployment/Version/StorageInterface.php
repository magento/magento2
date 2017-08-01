<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\View\Deployment\Version;

/**
 * Persistence of deployment version of static files
 * @since 2.0.0
 */
interface StorageInterface
{
    /**
     * Retrieve version value from a persistent storage
     *
     * @return string
     * @throws \UnexpectedValueException Exception is thrown when unable to retrieve data from a storage
     * @since 2.0.0
     */
    public function load();

    /**
     * Store version value in a persistent storage
     *
     * @param string $data
     * @return void
     * @since 2.0.0
     */
    public function save($data);
}
