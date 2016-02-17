<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Application
 * @subpackage Resource
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

#require_once 'Zend/Application/Resource/ResourceAbstract.php';

#require_once 'Zend/Db/Table.php';

/**
 */

/**
 * Cache Manager resource
 *
 * Example configuration:
 * <pre>
 *   resources.multidb.defaultMetadataCache = "database"
 *
 *   resources.multidb.db1.adapter = "pdo_mysql"
 *   resources.multidb.db1.host = "localhost"
 *   resources.multidb.db1.username = "webuser"
 *   resources.multidb.db1.password = "XXXX"
 *   resources.multidb.db1.dbname = "db1"
 *   resources.multidb.db1.default = true
 *
 *   resources.multidb.db2.adapter = "pdo_pgsql"
 *   resources.multidb.db2.host = "example.com"
 *   resources.multidb.db2.username = "dba"
 *   resources.multidb.db2.password = "notthatpublic"
 *   resources.multidb.db2.dbname = "db2"
 * </pre>
 *
 * @category   Zend
 * @package    Zend_Application
 * @subpackage Resource
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Application_Resource_Multidb extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * Associative array containing all configured db's
     *
     * @var array
     */
    protected $_dbs = array();

    /**
     * An instance of the default db, if set
     *
     * @var null|Zend_Db_Adapter_Abstract
     */
    protected $_defaultDb;

    /**
     * Initialize the Database Connections (instances of Zend_Db_Table_Abstract)
     *
     * @return Zend_Application_Resource_Multidb
     */
    public function init()
    {
        $options = $this->getOptions();

        if (isset($options['defaultMetadataCache'])) {
            $this->_setDefaultMetadataCache($options['defaultMetadataCache']);
            unset($options['defaultMetadataCache']);
        }

        foreach ($options as $id => $params) {
            $adapter = $params['adapter'];
            $default = (int) (
                isset($params['isDefaultTableAdapter']) && $params['isDefaultTableAdapter']
                || isset($params['default']) && $params['default']
            );
            unset(
                $params['adapter'],
                $params['default'],
                $params['isDefaultTableAdapter']
            );

            $this->_dbs[$id] = Zend_Db::factory($adapter, $params);

            if ($default) {
                $this->_setDefault($this->_dbs[$id]);
            }
        }

        return $this;
    }

    /**
     * Determine if the given db(identifier) is the default db.
     *
     * @param  string|Zend_Db_Adapter_Abstract $db The db to determine whether it's set as default
     * @return boolean True if the given parameter is configured as default. False otherwise
     */
    public function isDefault($db)
    {
        if (!$db instanceof Zend_Db_Adapter_Abstract) {
            $db = $this->getDb($db);
        }

        return $db === $this->_defaultDb;
    }

    /**
     * Retrieve the specified database connection
     *
     * @param  null|string|Zend_Db_Adapter_Abstract $db The adapter to retrieve.
     *                                               Null to retrieve the default connection
     * @return Zend_Db_Adapter_Abstract
     * @throws Zend_Application_Resource_Exception if the given parameter could not be found
     */
    public function getDb($db = null)
    {
        if ($db === null) {
            return $this->getDefaultDb();
        }

        if (isset($this->_dbs[$db])) {
            return $this->_dbs[$db];
        }

        throw new Zend_Application_Resource_Exception(
            'A DB adapter was tried to retrieve, but was not configured'
        );
    }

    /**
     * Get the default db connection
     *
     * @param  boolean $justPickOne If true, a random (the first one in the stack)
     *                           connection is returned if no default was set.
     *                           If false, null is returned if no default was set.
     * @return null|Zend_Db_Adapter_Abstract
     */
    public function getDefaultDb($justPickOne = true)
    {
        if ($this->_defaultDb !== null) {
            return $this->_defaultDb;
        }

        if ($justPickOne) {
            return reset($this->_dbs); // Return first db in db pool
        }

        return null;
    }

    /**
     * Set the default db adapter
     *
     * @var Zend_Db_Adapter_Abstract $adapter Adapter to set as default
     */
    protected function _setDefault(Zend_Db_Adapter_Abstract $adapter)
    {
        Zend_Db_Table::setDefaultAdapter($adapter);
        $this->_defaultDb = $adapter;
    }

   /**
     * Set the default metadata cache
     *
     * @param string|Zend_Cache_Core $cache
     * @return Zend_Application_Resource_Multidb
     */
    protected function _setDefaultMetadataCache($cache)
    {
        $metadataCache = null;

        if (is_string($cache)) {
            $bootstrap = $this->getBootstrap();
            if ($bootstrap instanceof Zend_Application_Bootstrap_ResourceBootstrapper &&
                $bootstrap->hasPluginResource('CacheManager')
            ) {
                $cacheManager = $bootstrap->bootstrap('CacheManager')
                    ->getResource('CacheManager');
                if (null !== $cacheManager && $cacheManager->hasCache($cache)) {
                    $metadataCache = $cacheManager->getCache($cache);
                }
            }
        } else if ($cache instanceof Zend_Cache_Core) {
            $metadataCache = $cache;
        }

        if ($metadataCache instanceof Zend_Cache_Core) {
            Zend_Db_Table::setDefaultMetadataCache($metadataCache);
        }

        return $this;
    }
}
