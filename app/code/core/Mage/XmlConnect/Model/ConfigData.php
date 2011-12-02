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
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Configuration data model
 *
 * @category    Mage
 * @package     Mage_Xmlconnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Model_ConfigData extends Mage_Core_Model_Abstract
{
    /**
     * Default category
     */
    const DEFAULT_CATEGORY = 'default';

    /**
     * Configuration prefix
     */
    const CONFIG_PREFIX = 'app_';

    /**
     * Delete on update paths array
     *
     * @var array
     */
    protected $_deleteOnUpdate = array();

    /**
     * Configuration data
     *
     * @var array
     */
    protected $_configuration = array();

    /**
     * Initialize configuration data
     *
     * @return null
     */
    protected function _construct()
    {
        $this->_init('Mage_XmlConnect_Model_Resource_ConfigData');
    }

    /**
     * Create an array that will be stored in configuration data
     *
     * Create an array: application id with a prefix as key and
     * configuration data as value
     *
     * @param  $applicationId
     * @param  $configData
     * @return array
     */
    protected function _assignConfig($applicationId, $configData)
    {
        return array(self::CONFIG_PREFIX . $applicationId => $configData);
    }

    /**
     * Prepare posted data to store at configuration.
     *
     * Posted data have to be in predefined format:
     * - array('category:config/path/param' => 'value')
     * where : is a separator of category
     * - array('config/path/param' => 'value')
     * if key doesn't have a separator category will be set as default
     *
     * @param  $configuration posted data array
     * @return array configuration data array
     */
    protected function _prepareData($configuration)
    {
        $configData = array();
        foreach ($configuration as $key => $val) {
            list($category, $path) = explode(':', $key, 2) + array('', '');
            if (empty($path)) {
                $path = $category;
                $category = self::DEFAULT_CATEGORY;
            }
            $val = is_array($val) ? implode(',', $val) : $val;
            $configData[strtolower($category)][strtolower($path)] = $val;
        }
        return $configData;
    }

    /**
     * Prepare and set configuration data
     *
     * @param  $applicationId
     * @param array $configData
     * @param bool $replace
     * @return Mage_XmlConnect_Model_ConfigData
     */
    public function setConfigData($applicationId, array $configData, $replace = true)
    {
        $configData = $this->_prepareData($configData);
        $arrayToStore = $this->_assignConfig($applicationId, $configData);
        if ($replace) {
            $this->_configuration = array_merge($this->_configuration, $arrayToStore);
        } else {
            $this->_configuration = $this->_configuration + $arrayToStore;
        }
        return $this;
    }

    /**
     * Get configuration data
     *
     * @param bool $applicationId
     * @return array
     */
    public function getConfigData($applicationId = false)
    {
        if ($applicationId && isset($this->_configuration[self::CONFIG_PREFIX . $applicationId])) {
            return $this->_configuration[self::CONFIG_PREFIX . $applicationId];
        }
        return $this->_configuration;
    }

    /**
     * Save predefined configuration data
     *
     * @return Mage_XmlConnect_Model_ConfigData
     */
    public function initSaveConfig()
    {
        foreach ($this->_configuration as $application => $data) {
            $applicationId = str_ireplace(self::CONFIG_PREFIX, '', $application);
            $this->_deleteOnUpdate($applicationId);
            foreach ($data as $category => $config) {
                $this->saveConfig($applicationId, $config, $category);
            }
        }
        return $this;
    }

    /**
     * Save configuration data by given params
     *
     * @param  $applicationId
     * @param array $configData
     * @param string $category
     * @return Mage_XmlConnect_Model_ConfigData
     */
    public function saveConfig($applicationId, array $configData, $category = self::DEFAULT_CATEGORY)
    {
        foreach ($configData as $path => $value) {
            if (!is_scalar($value)) {
                Mage::throwException(Mage::helper('Mage_XmlConnect_Helper_Data')->__('Unsupported value type received'));
            }
            $this->getResource()->saveConfig($applicationId, $category, $path, $value);
        }
        return $this;
    }

    /**
     * Get delete on update array paths
     *
     * @return array
     */
    public function getDeleteOnUpdate()
    {
        return $this->_deleteOnUpdate;
    }

    /**
     * Set delete on update array paths
     *
     * @param array $pathsToDelete
     * @return Mage_XmlConnect_Model_ConfigData
     */
    public function setDeleteOnUpdate(array $pathsToDelete)
    {
        $this->_deleteOnUpdate = array_merge($this->_deleteOnUpdate, $pathsToDelete);
        return $this;
    }

    /**
     * @param  $applicationId
     * @return Mage_XmlConnect_Model_ConfigData
     */
    protected function _deleteOnUpdate($applicationId)
    {
        foreach ($this->_deleteOnUpdate as $category => $path) {
            $this->getResource()->deleteConfig($applicationId, $category, $path);
        }
        return $this;
    }
}
