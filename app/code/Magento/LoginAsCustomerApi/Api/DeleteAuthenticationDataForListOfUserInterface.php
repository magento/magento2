<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerApi\Api;

/**
 * Delete authentication data by list of user id
 */
interface DeleteAuthenticationDataForListOfUserInterface
{
    /**
     * Delete authentication data by list of user id
     *
     * @param array $userIds
     * @return void
     */
    public function execute(array $userIds): void;
}
