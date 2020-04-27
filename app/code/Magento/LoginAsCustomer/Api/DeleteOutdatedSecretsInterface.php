<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Api;

/**
 * Delete outdated secret records
 *
 * @api
 */
interface DeleteOutdatedSecretsInterface
{
    /**
     * Delete outdated secret records
     */
    public function execute(): void;
}
