<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Api;

/**
 * Interface DomainManagerInterface
 * Manage downloadable domains whitelist.
 *
 * @api
 */
interface DomainManagerInterface
{
    /**
     * Get the whitelist.
     *
     * @return array
     */
    public function getDomains(): array;

    /**
     * Add host to the whitelist.
     *
     * @param array $hosts
     * @return void
     */
    public function addDomains(array $hosts): void;

    /**
     * Remove host from the whitelist.
     *
     * @param array $hosts
     * @return void
     */
    public function removeDomains(array $hosts): void;
}
