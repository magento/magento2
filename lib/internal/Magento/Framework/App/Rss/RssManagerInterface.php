<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\App\Rss;

/**
 * Interface RssManagerInterface
 * @package Magento\Framework\App\Rss
 */
interface RssManagerInterface
{
    /**
     * Get Data Provider by type
     * @param string $type
     * @return DataProviderInterface
     */
    public function getProvider($type);

    /**
     * Get all registered providers
     *
     * @return array
     */
    public function getProviders();
}
