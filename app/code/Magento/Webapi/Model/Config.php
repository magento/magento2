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
namespace Magento\Webapi\Model;

use Magento\Webapi\Model\Cache\Type;
use Magento\Webapi\Model\Config\Reader;

/**
 * Web API Config Model.
 *
 * This is a parent class for storing information about Web API. Most of it is needed by REST.
 */
class Config
{
    const CACHE_ID = 'webapi';

    /**
     * Pattern for Web API interface name.
     */
    const SERVICE_CLASS_PATTERN = '/^(.+?)\\\\(.+?)\\\\Service\\\\(V\d+)+(\\\\.+)Interface$/';

    /**
     * @var \Magento\Framework\App\Cache\Type\Config
     */
    protected $_configCacheType;

    /**
     * @var Reader
     */
    protected $_configReader;

    /**
     * Module configuration reader
     *
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $_moduleReader;

    /**
     * @var array
     */
    protected $_services;

    /**
     * @param Type $configCacheType
     * @param Reader $configReader
     */
    public function __construct(Type $configCacheType, Reader $configReader)
    {
        $this->_configCacheType = $configCacheType;
        $this->_configReader = $configReader;
    }

    /**
     * Return services loaded from cache if enabled or from files merged previously
     *
     * @return array
     */
    public function getServices()
    {
        if (null === $this->_services) {
            $services = $this->_loadFromCache();
            if ($services && is_string($services)) {
                $this->_services = unserialize($services);
            } else {
                $this->_services = $this->_configReader->read();
                $this->_saveToCache(serialize($this->_services));
            }
        }
        return $this->_services;
    }

    /**
     * Load services from cache
     *
     * @return string|bool
     */
    protected function _loadFromCache()
    {
        return $this->_configCacheType->load(self::CACHE_ID);
    }

    /**
     * Save services into the cache
     *
     * @param string $data serialized version of the webapi registry
     * @return $this
     */
    protected function _saveToCache($data)
    {
        $this->_configCacheType->save($data, self::CACHE_ID, array(\Magento\Webapi\Model\Cache\Type::CACHE_TAG));
        return $this;
    }
}
