<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
    public function save($data, $identifier, $tags = array(), $lifeTime = null)
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
    public function clean($tags = array())
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
