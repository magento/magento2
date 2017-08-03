<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Rss;

/**
 * Interface RssManagerInterface
 * @package Magento\Framework\App\Rss
 * @since 2.0.0
 */
interface RssManagerInterface
{
    /**
     * Get Data Provider by type
     * @param string $type
     * @return DataProviderInterface
     * @since 2.0.0
     */
    public function getProvider($type);

    /**
     * Get all registered providers
     *
     * @return array
     * @since 2.0.0
     */
    public function getProviders();
}
