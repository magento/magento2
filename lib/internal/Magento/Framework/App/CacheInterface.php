<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types = 1);

namespace Magento\Framework\App;

/**
 * System cache model interface
 *
 * @api
 * @since 100.0.2
 */
interface CacheInterface
{
    /**
     * Get cache frontend API object
     *
     * @return \Magento\Framework\Cache\FrontendInterface
     */
    public function getFrontend();

    /**
     * Load Data from Cache by ID
     *
     * @param string $identifier
     * @return bool|string
     */
    public function load($identifier);

    /**
     * Save data
     *
     * @param string $data
     * @param string $identifier
     * @param array $tags
     * @param int $lifeTime
     * @return bool
     */
    public function save($data, $identifier, $tags = [], $lifeTime = null);

    /**
     * Remove cached data by identifier
     *
     * @param string $identifier
     * @return bool
     */
    public function remove($identifier);

    /**
     * Clean cached data by specific tag
     *
     * @param array $tags
     * @return bool
     */
    public function clean($tags = []);
}
