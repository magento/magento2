<?php
/**
 * Application config storage
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\Config;

abstract class AbstractStorage implements \Magento\Core\Model\Config\StorageInterface
{
    /**
     * Cache storage object
     *
     * @var \Magento\Core\Model\Config\Cache
     */
    protected $_cache;

    /**
     * Configuration loader
     *
     * @var \Magento\Core\Model\Config\LoaderInterface
     */
    protected $_loader;

    /**
     * Configuration loader
     *
     * @var \Magento\Core\Model\Config\BaseFactory
     */
    protected $_configFactory;

    /**
     * @param \Magento\Core\Model\Config\Cache $cache
     * @param \Magento\Core\Model\Config\LoaderInterface $loader
     * @param \Magento\Core\Model\Config\BaseFactory $factory
     */
    public function __construct(
        \Magento\Core\Model\Config\Cache $cache,
        \Magento\Core\Model\Config\LoaderInterface $loader,
        \Magento\Core\Model\Config\BaseFactory $factory
    ) {
        $this->_cache = $cache;
        $this->_loader = $loader;
        $this->_configFactory = $factory;
    }

    /**
     * Get loaded configuration
     *
     * @return \Magento\Core\Model\ConfigInterface
     */
    public function getConfiguration()
    {
        $config = $this->_cache->load();
        if (false === $config) {
            $config = $this->_configFactory->create('<config/>');
            $this->_loader->load($config);
        }
        return $config;
    }

    /**
     * Remove configuration cache
     */
    public function removeCache()
    {

    }
}
