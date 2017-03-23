<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\Module;

/**
 * Resource Model
 */
class ModuleResource extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb implements \Magento\Framework\Module\ResourceInterface
{
    /**
     * Database versions
     *
     * @var array
     */
    protected static $schemaVersions = null;

    /**
     * Resource data versions cache array
     *
     * @var array
     */
    protected static $dataVersions = null;

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
        if ($needType == 'db' && is_null(self::$schemaVersions) || $needType == 'data' && is_null(self::$dataVersions)) {
            self::$schemaVersions = [];
            // Db version column always exists
            self::$dataVersions = null;
            // Data version array will be filled only if Data column exist

            if ($this->getConnection()->isTableExists($this->getMainTable())) {
                $select = $this->getConnection()->select()->from($this->getMainTable(), '*');
                $rowset = $this->getConnection()->fetchAll($select);
                foreach ($rowset as $row) {
                    self::$schemaVersions[$row['module']] = $row['schema_version'];
                    if (array_key_exists('data_version', $row)) {
                        if (is_null(self::$dataVersions)) {
                            self::$dataVersions = [];
                        }
                        self::$dataVersions[$row['module']] = $row['data_version'];
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
        if (!$this->getConnection()) {
            return false;
        }
        $this->_loadVersion('db');
        return isset(self::$schemaVersions[$moduleName]) ? self::$schemaVersions[$moduleName] : false;
    }

    /**
     * {@inheritdoc}
     */
    public function setDbVersion($moduleName, $version)
    {
        $dbModuleInfo = ['module' => $moduleName, 'schema_version' => $version];

        if ($this->getDbVersion($moduleName)) {
            self::$schemaVersions[$moduleName] = $version;
            return $this->getConnection()->update(
                $this->getMainTable(),
                $dbModuleInfo,
                ['module = ?' => $moduleName]
            );
        } else {
            self::$schemaVersions[$moduleName] = $version;
            return $this->getConnection()->insert($this->getMainTable(), $dbModuleInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDataVersion($moduleName)
    {
        if (!$this->getConnection()) {
            return false;
        }
        $this->_loadVersion('data');
        return isset(self::$dataVersions[$moduleName]) ? self::$dataVersions[$moduleName] : false;
    }

    /**
     * {@inheritdoc}
     */
    public function setDataVersion($moduleName, $version)
    {
        $data = ['module' => $moduleName, 'data_version' => $version];

        if ($this->getDbVersion($moduleName) || $this->getDataVersion($moduleName)) {
            self::$dataVersions[$moduleName] = $version;
            $this->getConnection()->update($this->getMainTable(), $data, ['module = ?' => $moduleName]);
        } else {
            self::$dataVersions[$moduleName] = $version;
            $this->getConnection()->insert($this->getMainTable(), $data);
        }
    }
}
