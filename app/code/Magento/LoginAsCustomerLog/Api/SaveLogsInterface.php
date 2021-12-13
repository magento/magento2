<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerLog\Api;

/**
 * Save login as custom logs entities.
 *
 * @api
 * @since 100.4.0
 */
interface SaveLogsInterface
{
    /**
     * Save logs.
     *
     * @param \Magento\LoginAsCustomerLog\Api\Data\LogInterface[] $logs
     * @return void
     * @since 100.4.0
     */
    public function execute(array $logs): void;
}
