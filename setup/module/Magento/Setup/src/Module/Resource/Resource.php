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
namespace Magento\Setup\Module\Resource;

use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet;
use Magento\Setup\Framework\DB\Adapter\AdapterInterface;
use Magento\Setup\Module\ResourceInterface;

/**
 * Resource Resource Model
 */
class Resource implements ResourceInterface
{
    const MAIN_TABLE = 'core_resource';

    /**
     * Database versions
     *
     * @var array
     */
    protected static $versions = null;

    /**
     * Table prefix
     * @var string
     */
    protected $tablePrefix;

    /**
     * @param AdapterInterface $adapter
     */
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Set table prefix
     *
     * @param string $tablePrefix
     * @return void
     */
    public function setTablePrefix($tablePrefix)
    {
        $this->tablePrefix = $tablePrefix;
    }

    /**
     * Load schema/db version
     *
     * @return $this
     */
    protected function loadVersionDb()
    {
        self::$versions = array();
        // Db version column always exists

        if ($this->adapter->isTableExists($this->getMainTable())) {
            $select = $this->adapter->select()->from($this->getMainTable());
            $sql = new Sql($this->adapter);
            $selectString = $sql->getSqlStringForSqlObject($select);
            $results = $this->adapter->query($selectString);
            if ($results instanceof ResultSet\ResultSetInterface && $results->count()) {
                foreach ($results as $row) {
                    self::$versions[$row['code']] = $row['version'];
                }
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDbVersion($resName)
    {
        if (!$this->adapter) {
            return false;
        }
        $this->loadVersionDb();
        return isset(self::$versions[$resName]) ? self::$versions[$resName] : false;
    }

    /**
     * {@inheritdoc}
     */
    public function setDbVersion($resName, $version)
    {
        $dbModuleInfo = array('code' => $resName, 'version' => $version);

        if ($this->getDbVersion($resName)) {
            self::$versions[$resName] = $version;
            return $this->adapter->update(
                $this->getMainTable(),
                $dbModuleInfo,
                array('code = ?' => $resName)
            );
        } else {
            self::$versions[$resName] = $version;
            return $this->adapter->insert($this->getMainTable(), $dbModuleInfo);
        }
    }

    /**
     * Get name of the resources table.
     *
     * @return string
     */
    protected function getMainTable()
    {
        return $this->adapter->getTableName($this->tablePrefix . self::MAIN_TABLE);
    }
}
