<?php
/**
 * ACL cache
 *
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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Core_Model_Acl_Cache implements Magento_Acl_CacheInterface
{
    /**
     * Cache
     *
     * @var Mage_Core_Model_Cache_Type_Config
     */
    protected $_cache;

    /**
     * Cache key
     *
     * @var string
     */
    protected $_cacheKey;

    /**
     * @var Magento_Acl
     */
    protected $_acl = null;

    /**
     * @param Mage_Core_Model_Cache_Type_Config $cache
     * @param string $cacheKey
     */
    function __construct(Mage_Core_Model_Cache_Type_Config $cache, $cacheKey)
    {
        $this->_cache = $cache;
        $this->_cacheKey = $cacheKey;
    }

    /**
     * Check whether ACL object is in cache
     *
     * @return bool
     */
    public function has()
    {
        return null !== $this->_acl || $this->_cache->test($this->_cacheKey);
    }

    /**
     * Retrieve ACL object from cache
     *
     * @return Magento_Acl
     */
    public function get()
    {
        if (null == $this->_acl) {
            $this->_acl = unserialize($this->_cache->load($this->_cacheKey));
        }
        return $this->_acl;
    }

    /**
     * Save ACL object to cache
     *
     * @param Magento_Acl $acl
     */
    public function save(Magento_Acl $acl)
    {
        $this->_acl = $acl;
        $this->_cache->save(serialize($acl), $this->_cacheKey);
    }

    /**
     * Clear ACL instance cache
     */
    public function clean()
    {
        $this->_acl = null;
        $this->_cache->remove($this->_cacheKey);
    }
}
