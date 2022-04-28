<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\View\Deployment\Version;

/**
 * Persistence of deployment version of static files
 *
 * @api
 */
interface StorageInterface
{
    /**
     * Retrieve version value from a persistent storage
     *
     * @return string
     * @throws \UnexpectedValueException Exception is thrown when unable to retrieve data from a storage
     */
    public function load();

    /**
     * Store version value in a persistent storage
     *
     * @param string $data
     * @return void
     */
    public function save($data);
}
