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
 * @package     Mage_Backend
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Backend System Configuration reader.
 * Retrieves system configuration form layout from system.xml files. Merges configuration and caches it.
 *
 * @category    Mage
 * @package     Mage_Backend
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Backend_Model_Config_Structure_Reader
{
    const CACHE_SYSTEM_CONFIGURATION_STRUCTURE = 'backend_system_configuration_structure';

    /**
     * @var Mage_Core_Model_Cache
     */
    protected $_cache;


    /**
     * @var Mage_Core_Model_Config
     */
    protected $_appConfig;

    /**
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        $this->_appConfig = isset($data['config']) ? $data['config'] : Mage::getConfig();
        $this->_cache = isset($data['cache']) ? $data['cache'] : Mage::app()->getCacheInstance();
    }

    /**
     * Load system configuration
     *
     * @return Mage_Backend_Model_Config_Structure
     */
    public function getConfiguration()
    {
        if ($this->_cache->canUse('config')) {
            $cache = $this->_cache->load(self::CACHE_SYSTEM_CONFIGURATION_STRUCTURE);
            if ($cache) {
                return unserialize($cache);
            }
        }

        $fileNames = $this->_appConfig->getModuleConfigurationFiles('adminhtml' . DIRECTORY_SEPARATOR . 'system.xml');
        $config = $this->_appConfig->getModelInstance(
            'Mage_Backend_Model_Config_Structure', array('sourceFiles' => $fileNames)
        );

        if ($this->_cache->canUse('config')) {
            $this->_cache->save(
                serialize($config),
                self::CACHE_SYSTEM_CONFIGURATION_STRUCTURE,
                array(Mage_Core_Model_Config::CACHE_TAG)
            );
        }

        return $config;
    }
}
