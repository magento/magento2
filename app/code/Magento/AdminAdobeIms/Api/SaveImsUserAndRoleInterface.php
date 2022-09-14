<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\Api;

use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Interface SaveImsUserAndRoleInterface
 * Save Ims User & Role
 */
interface SaveImsUserAndRoleInterface
{
    /**
     * Add Admin Adobe IMS User with Default Role i.e "Adobe Ims" & No Permissions
     *
     * @param array $profile
     * @return void
     * @throws CouldNotSaveException
     */
    public function save(array $profile): void;
}
