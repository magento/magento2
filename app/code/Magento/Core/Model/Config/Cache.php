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

namespace Magento\Core\Model\Config;

class Cache
{
    /**
     * Config cache id
     *
     * @var string
     */
    protected $_cacheId = 'config_global';

    /**
     * Container factory model
     *
     * @var \Magento\Core\Model\Config\BaseFactory
     */
    protected $_containerFactory;

    /**
     * @var \Magento\App\Cache\Type\Config
     */
    protected $_configCacheType;

    /**
     * Cache lifetime in seconds
     *
     * @var int
     */
    protected $_cacheLifetime;

    /**
     * Config container
     *
     * @var \Magento\Core\Model\Config\Base
     */
    protected $_loadedConfig = null;

    /**
     * @param \Magento\App\Cache\Type\Config $configCacheType
     * @param \Magento\Core\Model\Config\BaseFactory $containerFactory
     */
    public function __construct(
        \Magento\App\Cache\Type\Config $configCacheType,
        \Magento\Core\Model\Config\BaseFactory $containerFactory
    ) {
        $this->_containerFactory = $containerFactory;
        $this->_configCacheType = $configCacheType;
    }

    /**
     * Set cache lifetime
     *
     * @param int $lifetime
     * @return void
     */
    public function setCacheLifetime($lifetime)
    {
        $this->_cacheLifetime = $lifetime;
    }

    /**
     * Retrieve cache lifetime
     *
     * @return int
     */
    public function getCacheLifeTime()
    {
        return $this->_cacheLifetime;
    }

    /**
     * @return \Magento\App\ConfigInterface|bool
     */
    public function load()
    {
        if (!$this->_loadedConfig) {
            $config = $this->_configCacheType->load($this->_cacheId);
            if ($config) {
                $this->_loadedConfig = $this->_containerFactory->create($config);
            }
        }
        return $this->_loadedConfig ? : false;
    }

    /**
     * Save config cache
     *
     * @param \Magento\Core\Model\Config\Base $config
     * @return void
     */
    public function save(\Magento\Core\Model\Config\Base $config)
    {
        $this->_configCacheType->save(
            $config->getNode()->asNiceXml('', false), $this->_cacheId, array(), $this->_cacheLifetime
        );
    }

    /**
     * Clean cached data
     *
     * @return bool
     */
    public function clean()
    {
        $this->_loadedConfig = null;
        return $this->_configCacheType->clean();
    }
}
