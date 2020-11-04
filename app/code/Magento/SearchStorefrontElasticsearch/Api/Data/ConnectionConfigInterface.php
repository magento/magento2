<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SearchStorefrontElasticsearch\Api\Data;

interface ConnectionConfigInterface
{
    /**
     * Get elasticsearch host name
     *
     * @return string
     */
    public function getServerHostname() : string;

    /**
     * Get elasticsearch server port
     *
     * @return string
     */
    public function getServerPort() : string;

    /**
     * Get elasticsearch index prefix
     *
     * @return string
     */
    public function getIndexPrefix() : string;

    /**
     * Enable auth
     *
     * @return int
     */
    public function getEnableAuth() : int;

    /**
     * Get elasticsearch user name
     *
     * @return string
     */
    public function getUsername() : string;

    /**
     * Get elasticsearch user password
     *
     * @return string
     */
    public function getPassword() : string;

    /**
     * Get elasticsearch timeout
     *
     * @return int
     */
    public function getTimeout() : int;

    /**
     * Get elasticsearch engine
     *
     * @return string
     */
    public function getEngine() : string;

    /**
     * Get elasticsearch engine
     *
     * @return string
     */
    public function getMinimumShouldMatch() : string;

    /**
     * @return array
     */
    public function getConfig() : array;
}
