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
 * @category   Varien
 * @package    Varien_Db
 * @copyright  Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Varien_Db_Adapter_Mysqli extends Zend_Db_Adapter_Mysqli
{
    const ISO_DATE_FORMAT       = 'yyyy-MM-dd';
    const ISO_DATETIME_FORMAT   = 'yyyy-MM-dd HH-mm-ss';

    /**
     * Creates a real connection to the database with multi-query capability.
     *
     * @return void
     * @throws Zend_Db_Adapter_Mysqli_Exception
     */
    protected function _connect()
    {
        if ($this->_connection) {
            return;
        }
        if (!extension_loaded('mysqli')) {
            throw new Zend_Db_Adapter_Exception('mysqli extension is not installed');
        }
        // Suppress connection warnings here.
        // Throw an exception instead.
        @$conn = new mysqli();
        if (false===$conn || mysqli_connect_errno()) {
            throw new Zend_Db_Adapter_Mysqli_Exception(mysqli_connect_errno());
        }

        $conn->init();
        $conn->options(MYSQLI_OPT_LOCAL_INFILE, true);
        #$conn->options(MYSQLI_CLIENT_MULTI_QUERIES, true);

        $port = !empty($this->_config['port']) ? $this->_config['port'] : null;
        $socket = !empty($this->_config['unix_socket']) ? $this->_config['unix_socket'] : null;
        // socket specified in host config
        if (strpos($this->_config['host'], '/')!==false) {
            $socket = $this->_config['host'];
            $this->_config['host'] = null;
        } elseif (strpos($this->_config['host'], ':')!==false) {
            list($this->_config['host'], $port) = explode(':', $this->_config['host']);
        }

        #echo "<pre>".print_r($this->_config,1)."</pre>"; die;
        @$conn->real_connect(
            $this->_config['host'],
            $this->_config['username'],
            $this->_config['password'],
            $this->_config['dbname'],
            $port,
            $socket
        );
        if (mysqli_connect_errno()) {
            throw new Zend_Db_Adapter_Mysqli_Exception(mysqli_connect_error());
        }

        $this->_connection = $conn;

        /** @link http://bugs.mysql.com/bug.php?id=18551 */
        $this->_connection->query("SET SQL_MODE=''");
    }

    /**
     * Run RAW Query
     *
     * @param string $sql
     * @return Zend_Db_Statement_Interface
     */
    public function raw_query($sql)
    {
        $timeoutMessage = 'SQLSTATE[HY000]: General error: 1205 Lock wait timeout exceeded; try restarting transaction';
        $tries = 0;
        do {
            $retry = false;
            try {
                $this->clear_result();
                $result = $this->getConnection()->query($sql);
                $this->clear_result();
            } catch (Exception $e) {
                if ($tries < 10 && $e->getMessage() == $timeoutMessage) {
                    $retry = true;
                    $tries++;
                } else {
                    throw $e;
                }
            }
        } while ($retry);

        return $result;
    }


    public function convertDate($date)
    {
        if ($date instanceof Zend_Date) {
            return $date->toString(self::ISO_DATE_FORMAT);
        }
        return strftime('%Y-%m-%d', strtotime($date));
    }

    public function convertDateTime($datetime)
    {
        if ($datetime instanceof Zend_Date) {
            return $datetime->toString(self::ISO_DATETIME_FORMAT);
        }
        return strftime('%Y-%m-%d %H:%M:%S', strtotime($datetime));
    }



    public function raw_fetchRow($sql, $field=null)
    {
        if (!$result = $this->raw_query($sql)) {
            return false;
        }
        if (!$row = $result->fetch_assoc()) {
            return false;
        }
        if (empty($field)) {
            return $row;
        } else {
            return isset($row[$field]) ? $row[$field] : false;
        }
    }

    public function multi_query($sql)
    {
        $this->beginTransaction();
        try {
            $this->clear_result();
            if ($this->getConnection()->multi_query($sql)) {
                $this->clear_result();
                $this->commit();
            } else {
                throw new Zend_Db_Adapter_Mysqli_Exception('multi_query: '.$this->getConnection()->error);
            }
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }

        return true;
    }

    public function clear_result()
    {
        while ($this->getConnection()->next_result()) {
            if ($result = $this->getConnection()->store_result()) {
                $result->free_result();
            } elseif($this->getConnection()->error) {
                throw new Zend_Db_Adapter_Mysqli_Exception('clear_result: '.$this->getConnection()->error);
            }
        }

    }

    public function dropForeignKey($table, $fk)
    {
        $create = $this->raw_fetchRow("show create table `$table`", 'Create Table');
        if (strpos($create, "CONSTRAINT `$fk` FOREIGN KEY (")!==false) {
            return $this->raw_query("ALTER TABLE `$table` DROP FOREIGN KEY `$fk`");
        }
        return true;
    }

    public function dropKey($table, $key)
    {
        $create = $this->raw_fetchRow("show create table `$table`", 'Create Table');
        if (strpos($create, "KEY `$key` (")!==false) {
            return $this->raw_query("ALTER TABLE `$table` DROP KEY `$key`");
        }
        return true;
    }

    /**
     * ADD CONSTRAINT
     *
     *
     * @param string $fkName
     * @param string $tableName
     * @param string $keyName
     * @param string $refTableName
     * @param string $refKeyName
     * @param string $onUpdate
     * @param string $onDelete
     */
    public function addConstraint($fkName, $tableName, $keyName, $refTableName,
        $refKeyName, $onDelete = 'cascade', $onUpdate = 'cascade')
    {
        if (substr($fkName, 0, 3) != 'FK_') {
            $fkName = 'FK_' . $fkName;
        }

        $sql = 'ALTER TABLE `'.$tableName.'` ADD CONSTRAINT `'.$fkName.'`'
            . 'FOREIGN KEY (`'.$keyName.'`) REFERENCES `'.$refTableName.'` (`'.$refKeyName.'`)';
        if (!is_null($onDelete)) {
            $sql .= ' ON DELETE ' . strtoupper($onDelete);
        }
        if (!is_null($onUpdate)) {
            $sql .= ' ON UPDATE ' . strtoupper($onUpdate);
        }

        return $this->raw_query($sql);
    }

    public function tableColumnExists($tableName, $columnName)
    {
        foreach ($this->fetchAll('DESCRIBE `'.$tableName.'`') as $row) {
            if ($row['Field'] == $columnName) {
                return true;
            }
        }
        return false;
    }

    public function addColumn($tableName, $columnName, $definition)
    {
        if ($this->tableColumnExists($tableName, $columnName)) {
            return true;
        }
        $result = $this->raw_query("alter table `$tableName` add column `$columnName` ".$definition);
        return $result;
    }

    public function dropColumn($tableName, $columnName)
    {
        if (!$this->tableColumnExists($tableName, $columnName)) {
            return true;
        }

        $create = $this->raw_fetchRow('SHOW CREATE TABLE `'.$tableName.'`', 'Create Table');

        $alterDrop = array();
        $alterDrop[] = 'DROP COLUMN `'.$columnName.'`';

        /**
         * find foreign keys for column
         */
        $matches = array();
        preg_match_all('/CONSTRAINT `([^`]*)` FOREIGN KEY \(`([^`]*)`\)/', $create, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            if ($match[2] == $columnName) {
                $alterDrop[] = 'DROP FOREIGN KEY `'.$match[1].'`';
            }
        }

        return $this->raw_query('ALTER TABLE `'.$tableName.'` ' . join(', ', $alterDrop));
    }

    /**
     * Creates and returns a new Zend_Db_Select object for this adapter.
     *
     * @return Varien_Db_Select
     */
    public function select()
    {
        return new Varien_Db_Select($this);
    }
}
