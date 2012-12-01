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
 * @package     Mage_Install
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Abstract resource data model
 *
 * @category    Mage
 * @package     Mage_Install
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Install_Model_Installer_Db_Abstract
{
    /**
     *  Adapter instance
     *
     * @var Varien_Db_Adapter_Interface
     */
    protected $_connection;

    /**
     *  Connection configuration
     *
     * @var array
     */
    protected $_connectionData;

    /**
     *  Connection configuration
     *
     * @var array
     */
    protected $_configData;


    /**
     * Return the name of DB model from config
     *
     * @return string
     */
    public function getModel()
    {
        return $this->_configData['db_model'];
    }


    /**
     * Return the DB type from config
     *
     * @return string
     */
    public function getType()
    {
        return $this->_configData['db_type'];
    }

    /**
     * Set configuration data
     *
     * @param array $config the connection configuration
     */
    public function setConfig($config)
    {
        $this->_configData = $config;
    }

    /**
     * Retrieve connection data from config
     *
     * @return array
     */
    public function getConnectionData()
    {
        if (!$this->_connectionData) {
            $connectionData = array(
                'host'      => $this->_configData['db_host'],
                'username'  => $this->_configData['db_user'],
                'password'  => $this->_configData['db_pass'],
                'dbname'    => $this->_configData['db_name'],
                'pdoType'   => $this->getPdoType()
            );
            $this->_connectionData = $connectionData;
        }
        return $this->_connectionData;
    }

    /**
     * Check InnoDB support
     *
     * @return bool
     */
    public function supportEngine()
    {
        return true;
    }

    /**
     * Create new connection with custom config
     *
     * @return Varien_Db_Adapter_Interface
     */
    protected function _getConnection()
    {
        if (!isset($this->_connection)) {
            $resource   = Mage::getSingleton('Mage_Core_Model_Resource');
            $connection = $resource->createConnection('install', $this->getType(), $this->getConnectionData());
            $this->_connection = $connection;
        }
        return $this->_connection;
    }

    /**
     * Return pdo type
     *
     * @return null
     */
    public function getPdoType()
    {
        return null;
    }

    /**
     * Retrieve required PHP extension list for database
     *
     * @return array
     */
    public function getRequiredExtensions()
    {
        $extensions = array();
        $configExt = (array)Mage::getConfig()->getNode(sprintf('install/databases/%s/extensions', $this->getModel()));
        foreach ($configExt as $name=>$value) {
            $extensions[] = $name;
        }
        return $extensions;
    }

    /**
     * Clean database
     *
     * @param SimpleXMLElement $config
     * @return Mage_Install_Model_Installer_Db_Abstract
     */
    abstract public function cleanUpDatabase(SimpleXMLElement $config);
}
