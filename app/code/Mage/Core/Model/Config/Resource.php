<?php
/**
 * Resource configuration. Uses application configuration to retrieve resource information.
 * Uses latest loaded configuration object to make resource connection available on early stages of bootstrapping.
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
class Mage_Core_Model_Config_Resource
{
    /**
     * @var Mage_Core_Model_ConfigInterface
     */
    protected $_config;

    /**
     * @param Mage_Core_Model_ConfigInterface $config
     */
    public function __construct(Mage_Core_Model_ConfigInterface $config)
    {
        $this->_config = $config;
    }

    /**
     * Set application config
     *
     * @param Mage_Core_Model_ConfigInterface $config
     */
    public function setConfig(Mage_Core_Model_ConfigInterface $config)
    {
        $this->_config = $config;
    }

    /**
     * Get resource configuration for resource name
     *
     * @param string $name
     * @return Varien_Simplexml_Object
     */
    public function getResourceConfig($name)
    {
        return $this->_config->getNode('global/resources/' . $name);
    }

    /**
     * Retrieve resource connection configuration by name
     *
     * @param $name
     * @return Varien_Simplexml_Element
     */
    public function getResourceConnectionConfig($name)
    {
        $config = $this->getResourceConfig($name);
        if ($config) {
            $conn = $config->connection;
            if ($conn) {
                if (!empty($conn->use)) {
                    return $this->getResourceConnectionConfig((string)$conn->use);
                } else {
                    return $conn;
                }
            }
        }
        return false;
    }

    /**
     * Retrieve reosurce type configuration
     *
     * @param $type
     * @return Varien_Simplexml_Element
     */
    public function getResourceTypeConfig($type)
    {
        return $this->_config->getNode('global/resource/connection/types/' . $type);
    }

    /**
     * Retrieve database table prefix
     *
     * @return string
     */
    public function getTablePrefix()
    {
        return (string) $this->_config->getNode('global/resources/db/table_prefix');
    }

    /**
     * Retrieve resource connection model name
     *
     * @param string $moduleName
     * @return string
     */
    public function getResourceConnectionModel($moduleName = null)
    {
        $config = null;
        if (!is_null($moduleName)) {
            $setupResource = $moduleName . '_setup';
            $config        = $this->getResourceConnectionConfig($setupResource);
        }
        if (!$config) {
            $config = $this->getResourceConnectionConfig(Mage_Core_Model_Resource::DEFAULT_SETUP_RESOURCE);
        }

        return (string) $config->model;
    }
}
