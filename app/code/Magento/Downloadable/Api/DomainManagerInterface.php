<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Api;

/**
 * Interface DomainManagerInterface
 * Manage downloadable domains whitelist in the environment config.
 */
interface DomainManagerInterface
{
    /**
     * Get the whitelist.
     *
     * @return array
     */
    public function getEnvDomainWhitelist();

    /**
     * Add host to the whitelist.
     *
     * @param array $hosts
     * @return void
     */
    public function addEnvDomains($hosts);

    /**
     * Remove host from the whitelist.
     *
     * @param array $hosts
     * @return void
     */
    public function removeEnvDomains($hosts);
}
