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
 * @category    Mage
 * @package     Mage_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * System cache proxy model
  */
class Mage_Core_Model_Cache_Proxy implements Mage_Core_Model_CacheInterface
{
    /**
     * @var Magento_ObjectManager
     */
    protected  $_objectManager;

    /**
     * @var Mage_Core_Model_Cache
     */
    protected  $_cache;

    /**
     * @param Magento_ObjectManager $objectManager
     */
    public function __construct(Magento_ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create cache model
     *
     * @return Mage_Core_Model_Cache|mixed
     */
    protected function _getCache()
    {
        if (null == $this->_cache) {
            $this->_cache = $this->_objectManager->get('Mage_Core_Model_Cache');
        }
        return $this->_cache;
    }

    /**
     * Get cache frontend API object
     *
     * @return Zend_Cache_Core
     */
    public function getFrontend()
    {
        return $this->_getCache()->getFrontend();
    }

    /**
     * Load data from cache by id
     *
     * @param   string $id
     * @return  string
     */
    public function load($id)
    {
        return $this->_getCache()->load($id);
    }

    /**
     * Save data
     *
     * @param string $data
     * @param string $id
     * @param array $tags
     * @param int $lifeTime
     * @return bool
     */
    public function save($data, $id, $tags = array(), $lifeTime = null)
    {
        return $this->_getCache()->save($data, $id, $tags, $lifeTime);
    }

    /**
     * Remove cached data by identifier
     *
     * @param string $id
     * @return bool
     */
    public function remove($id)
    {
        return $this->_getCache()->remove($id);
    }

    /**
     * Clean cached data by specific tag
     *
     * @param array $tags
     * @return bool
     */
    public function clean($tags = array())
    {
        return $this->_getCache()->clean($tags);
    }

    /**
     * Clean cached data by specific tag
     *
     * @return bool
     */
    public function flush()
    {
        return $this->_getCache()->flush();
    }

    /**
     * Get adapter for database cache backend model
     *
     * @return Zend_Db_Adapter_Abstract
     */
    public function getDbAdapter()
    {
        return $this->_getCache()->getDbAdapter();
    }

    /**
     * Save cache usage options
     *
     * @param array $options
     * @return Mage_Core_Model_Cache
     */
    public function saveOptions($options)
    {
        return $this->_getCache()->saveOptions($options);
    }

    /**
     * Check if cache can be used for specific data type
     *
     * @param string $typeCode
     * @return bool
     */
    public function canUse($typeCode)
    {
        return $this->_getCache()->canUse($typeCode);
    }

    /**
     * Disable cache usage for specific data type
     *
     * @param string $typeCode
     * @return Mage_Core_Model_Cache
     */
    public function banUse($typeCode)
    {
        return $this->_getCache()->banUse($typeCode);
    }

    /**
     * Enable cache usage for specific data type
     *
     * @param string $typeCode
     * @return Mage_Core_Model_Cache
     */
    public function allowUse($typeCode)
    {
        return $this->_getCache()->allowUse($typeCode);
    }

    /**
     * Get cache tags by cache type from configuration
     *
     * @param string $type
     * @return array
     */
    public function getTagsByType($type)
    {
        return $this->_getCache()->getTagsByType($type);
    }

    /**
     * Get information about all declared cache types
     *
     * @return array
     */
    public function getTypes()
    {
        return $this->_getCache()->getTypes();
    }

    /**
     * Get array of all invalidated cache types
     *
     * @return array
     */
    public function getInvalidatedTypes()
    {
        return $this->_getCache()->getInvalidatedTypes();
    }

    /**
     * Mark specific cache type(s) as invalidated
     *
     * @param string|array $typeCode
     * @return Mage_Core_Model_Cache
     */
    public function invalidateType($typeCode)
    {
        return $this->_getCache()->invalidateType($typeCode);
    }

    /**
     * Clean cached data for specific cache type
     *
     * @param string $typeCode
     * @return Mage_Core_Model_Cache
     */
    public function cleanType($typeCode)
    {
        return $this->_getCache()->cleanType($typeCode);
    }
}
