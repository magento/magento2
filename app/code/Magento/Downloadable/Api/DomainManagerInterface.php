<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Api;

/**
 * Manage downloadable domains whitelist.
 */
interface DomainManagerInterface
{
    /**
     * Get the domains whitelist.
     *
     * @return array
     */
    public function getDomains(): array;

    /**
     * Add hosts to the domains whitelist.
     *
     * @param array $hosts
     * @return void
     */
    public function addDomains(array $hosts): void;

    /**
     * Remove hosts from the domains whitelist.
     *
     * @param array $hosts
     * @return void
     */
    public function removeDomains(array $hosts): void;
}
