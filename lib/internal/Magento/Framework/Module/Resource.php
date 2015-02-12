<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\Module;


/**
 * Resource Model
 */
class Resource extends \Magento\Framework\Model\Resource\Db\AbstractDb implements \Magento\Framework\Module\ResourceInterface
{
    /**
     * Database versions
     *
     * @var array
     */
    protected static $schema_versions = null;

    /**
     * Resource data versions cache array
     *
     * @var array
     */
    protected static $_dataVersions = null;

    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('setup_module', 'module');
    }

    /**
     * Fill static versions arrays.
     * This routine fetches Db and Data versions of at once to optimize sql requests. However, when upgrading, it's
     * possible that 'data' column will be created only after all Db installs are passed. So $neededType contains
     * information on main purpose of calling this routine, and even when 'data' column is absent - it won't require
     * reissuing new sql just to get 'db' version of module.
     *
     * @param string $needType Can be 'db' or 'data'
     * @return $this
     */
    protected function _loadVersion($needType)
    {
        if ($needType == 'db' && is_null(self::$schema_versions) || $needType == 'data' && is_null(self::$_dataVersions)) {
            self::$schema_versions = [];
            // Db version column always exists
            self::$_dataVersions = null;
            // Data version array will be filled only if Data column exist

            if ($this->_getReadAdapter()->isTableExists($this->getMainTable())) {
                $select = $this->_getReadAdapter()->select()->from($this->getMainTable(), '*');
                $rowset = $this->_getReadAdapter()->fetchAll($select);
                foreach ($rowset as $row) {
                    self::$schema_versions[$row['module']] = $row['schema_version'];
                    if (array_key_exists('data_version', $row)) {
                        if (is_null(self::$_dataVersions)) {
                            self::$_dataVersions = [];
                        }
                        self::$_dataVersions[$row['module']] = $row['data_version'];
                    }
                }
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDbVersion($moduleName)
    {
        if (!$this->_getReadAdapter()) {
            return false;
        }
        $this->_loadVersion('db');
        return isset(self::$schema_versions[$moduleName]) ? self::$schema_versions[$moduleName] : false;
    }

    /**
     * {@inheritdoc}
     */
    public function setDbVersion($moduleName, $version)
    {
        $dbModuleInfo = ['module' => $moduleName, 'schema_version' => $version];

        if ($this->getDbVersion($moduleName)) {
            self::$schema_versions[$moduleName] = $version;
            return $this->_getWriteAdapter()->update(
                $this->getMainTable(),
                $dbModuleInfo,
                ['module = ?' => $moduleName]
            );
        } else {
            self::$schema_versions[$moduleName] = $version;
            return $this->_getWriteAdapter()->insert($this->getMainTable(), $dbModuleInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDataVersion($moduleName)
    {
        if (!$this->_getReadAdapter()) {
            return false;
        }
        $this->_loadVersion('data');
        return isset(self::$_dataVersions[$moduleName]) ? self::$_dataVersions[$moduleName] : false;
    }

    /**
     * {@inheritdoc}
     */
    public function setDataVersion($moduleName, $version)
    {
        $data = ['module' => $moduleName, 'data_version' => $version];

        if ($this->getDbVersion($moduleName) || $this->getDataVersion($moduleName)) {
            self::$_dataVersions[$moduleName] = $version;
            $this->_getWriteAdapter()->update($this->getMainTable(), $data, ['module = ?' => $moduleName]);
        } else {
            self::$_dataVersions[$moduleName] = $version;
            $this->_getWriteAdapter()->insert($this->getMainTable(), $data);
        }
    }
}
