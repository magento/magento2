<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * System cache model
 * support id and tags prefix support,
 */
namespace Magento\Framework\App;

class Cache implements CacheInterface
{
    /**
     * @var string
     */
    protected $_frontendIdentifier = \Magento\Framework\App\Cache\Frontend\Pool::DEFAULT_FRONTEND_ID;

    /**
     * @var \Magento\Framework\App\Cache\Frontend\Pool
     */
    protected $_frontendPool;

    /**
     * Cache frontend API
     *
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    protected $_frontend;

    /**
     * @param \Magento\Framework\App\Cache\Frontend\Pool $frontendPool
     */
    public function __construct(\Magento\Framework\App\Cache\Frontend\Pool $frontendPool)
    {
        $this->_frontendPool = $frontendPool;
        $this->_frontend = $frontendPool->get($this->_frontendIdentifier);
    }

    /**
     * Get cache frontend API object
     *
     * @return \Magento\Framework\Cache\FrontendInterface
     */
    public function getFrontend()
    {
        return $this->_frontend;
    }

    /**
     * Load data from cache by id
     *
     * @param  string $identifier
     * @return string
     */
    public function load($identifier)
    {
        return $this->_frontend->load($identifier);
    }

    /**
     * Save data
     *
     * @param string $data
     * @param string $identifier
     * @param array $tags
     * @param int $lifeTime
     * @return bool
     */
    public function save($data, $identifier, $tags = [], $lifeTime = null)
    {
        return $this->_frontend->save((string)$data, $identifier, $tags, $lifeTime);
    }

    /**
     * Remove cached data by identifier
     *
     * @param string $identifier
     * @return bool
     */
    public function remove($identifier)
    {
        return $this->_frontend->remove($identifier);
    }

    /**
     * Clean cached data by specific tag
     *
     * @param array $tags
     * @return bool
     */
    public function clean($tags = [])
    {
        if ($tags) {
            $result = $this->_frontend->clean(\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, (array)$tags);
        } else {
            /** @deprecated special case of cleaning by empty tags is deprecated after 2.0.0.0-dev42 */
            $result = false;
            /** @var $cacheFrontend \Magento\Framework\Cache\FrontendInterface */
            foreach ($this->_frontendPool as $cacheFrontend) {
                if ($cacheFrontend->clean()) {
                    $result = true;
                }
            }
        }
        return $result;
    }
}
