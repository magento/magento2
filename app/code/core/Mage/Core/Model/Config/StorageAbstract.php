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
abstract class Mage_Core_Model_Config_StorageAbstract implements Mage_Core_Model_Config_StorageInterface
{
    /**
     * Cache storage object
     *
     * @var Mage_Core_Model_Config_Cache
     */
    protected $_cache;

    /**
     * Configuration loader
     *
     * @var Mage_Core_Model_Config_LoaderInterface
     */
    protected $_loader;

    /**
     * Configuration loader
     *
     * @var Mage_Core_Model_Config_BaseFactory
     */
    protected $_configFactory;

    /**
     * @param Mage_Core_Model_Config_Cache $cache
     * @param Mage_Core_Model_Config_LoaderInterface $loader
     * @param Mage_Core_Model_Config_BaseFactory $factory
     */
    public function __construct(
        Mage_Core_Model_Config_Cache $cache,
        Mage_Core_Model_Config_LoaderInterface $loader,
        Mage_Core_Model_Config_BaseFactory $factory
    ) {
        $this->_cache = $cache;
        $this->_loader = $loader;
        $this->_configFactory = $factory;
    }

    /**
     * Get loaded configuration
     *
     * @return Mage_Core_Model_ConfigInterface
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
