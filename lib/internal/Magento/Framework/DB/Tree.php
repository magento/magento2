<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\DB;

use Magento\Framework\DB\Tree\Node;
use Magento\Framework\DB\Tree\NodeSet;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Magento Library
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 *
 * @deprecated Not used anymore.
 */
class Tree
{
    /**
     * @var string|int
     */
    private $_id;

    /**
     * @var int
     */
    private $_left;

    /**
     * @var int
     */
    private $_right;

    /**
     * @var int
     */
    private $_level;

    /**
     * @var int
     */
    private $_pid;

    /**
     * @var array
     */
    private $_nodesInfo = [];

    /**
     * Array of additional tables
     *
     * array(
     *  [$tableName] => array(
     *              ['joinCondition']
     *              ['fields']
     *          )
     * )
     *
     * @var array
     */
    private $_extTables = [];

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $_db;

    /**
     * @var string
     */
    private $_table;

    /**
     * @param array $config
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @deprecated Not used anymore.
     */
    public function __construct($config = [])
    {
        // set a \Zend_Db_Adapter connection
        if (!empty($config['db'])) {
            // convenience variable
            $connection = $config['db'];

            // use an object from the registry?
            if (is_string($connection)) {
                $connection = \Zend::registry($connection);
            }

            // make sure it's a \Magento\Framework\DB\Adapter\AdapterInterface
            if (!$connection instanceof \Magento\Framework\DB\Adapter\AdapterInterface) {
                throw new LocalizedException(
                    new Phrase('db object does not implement \Magento\Framework\DB\Adapter\AdapterInterface')
                );
            }

            // save the connection
            $this->_db = $connection;
            $conn = $this->_db->getConnection();
            if ($conn instanceof \PDO) {
                $conn->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
            }
        } else {
            throw new LocalizedException(
                new Phrase('The "db object" isn\'t set in config. Set the "db object" and try again.')
            );
        }

        if (!empty($config['table'])) {
            $this->setTable($config['table']);
        }

        if (!empty($config['id'])) {
            $this->setIdField($config['id']);
        } else {
            $this->setIdField('id');
        }

        if (!empty($config['left'])) {
            $this->setLeftField($config['left']);
        } else {
            $this->setLeftField('left_key');
        }

        if (!empty($config['right'])) {
            $this->setRightField($config['right']);
        } else {
            $this->setRightField('right_key');
        }

        if (!empty($config['level'])) {
            $this->setLevelField($config['level']);
        } else {
            $this->setLevelField('level');
        }

        if (!empty($config['pid'])) {
            $this->setPidField($config['pid']);
        } else {
            $this->setPidField('parent_id');
        }
    }

    /**
     * set name of id field
     *
     * @param string $name
     * @return $this
     *
     * @deprecated Not used anymore.
     */
    public function setIdField($name)
    {
        $this->_id = $name;
        return $this;
    }

    /**
     * set name of left field
     *
     * @param string $name
     * @return $this
     *
     * @deprecated Not used anymore.
     */
    public function setLeftField($name)
    {
        $this->_left = $name;
        return $this;
    }

    /**
     * set name of right field
     *
     * @param string $name
     * @return $this
     *
     * @deprecated Not used anymore.
     */
    public function setRightField($name)
    {
        $this->_right = $name;
        return $this;
    }

    /**
     * set name of level field
     *
     * @param string $name
     * @return $this
     *
     * @deprecated Not used anymore.
     */
    public function setLevelField($name)
    {
        $this->_level = $name;
        return $this;
    }

    /**
     * set name of pid Field
     *
     * @param string $name
     * @return $this
     *
     * @deprecated Not used anymore.
     */
    public function setPidField($name)
    {
        $this->_pid = $name;
        return $this;
    }

    /**
     * set table name
     *
     * @param string $name
     * @return $this
     *
     * @deprecated Not used anymore.
     */
    public function setTable($name)
    {
        $this->_table = $name;
        return $this;
    }

    /**
     * @return array
     *
     * @deprecated Not used anymore.
     */
    public function getKeys()
    {
        $keys = [];
        $keys['id'] = $this->_id;
        $keys['left'] = $this->_left;
        $keys['right'] = $this->_right;
        $keys['pid'] = $this->_pid;
        $keys['level'] = $this->_level;
        return $keys;
    }

    /**
     * Clear table and add root element
     *
     * @param array $data
     * @return string
     *
     * @deprecated Not used anymore.
     */
    public function clear($data = [])
    {
        // clearing table
        $this->_db->query('TRUNCATE ' . $this->_table);

        // prepare data for root element
        $data[$this->_pid] = 0;
        $data[$this->_left] = 1;
        $data[$this->_right] = 2;
        $data[$this->_level] = 0;

        try {
            $this->_db->insert($this->_table, $data);
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
        return $this->_db->lastInsertId();
    }

    /**
     * Get node information
     *
     * @param string|int $nodeId
     * @return array
     *
     * @deprecated Not used anymore.
     */
    public function getNodeInfo($nodeId)
    {
        if (empty($this->_nodesInfo[$nodeId])) {
            $sql = 'SELECT * FROM ' . $this->_table . ' WHERE ' . $this->_id . '=:id';
            $res = $this->_db->query($sql, ['id' => $nodeId]);
            $data = $res->fetch();
            $this->_nodesInfo[$nodeId] = $data;
        } else {
            $data = $this->_nodesInfo[$nodeId];
        }
        return $data;
    }

    /**
     * @param string|int $nodeId
     * @param array $data
     * @return false|string
     *
     * @deprecated Not used anymore.
     */
    public function appendChild($nodeId, $data)
    {
        $info = $this->getNodeInfo($nodeId);
        if (!$info) {
            return false;
        }

        $data[$this->_left] = $info[$this->_right];
        $data[$this->_right] = $info[$this->_right] + 1;
        $data[$this->_level] = $info[$this->_level] + 1;
        $data[$this->_pid] = $nodeId;

        // creating a place for the record being inserted
        if ($nodeId) {
            $this->_db->beginTransaction();
            try {
                $sql = 'UPDATE ' .
                    $this->_table .
                    ' SET' .
                    ' `' .
                    $this->_left .
                    '` = IF( `' .
                    $this->_left .
                    '` > :left,' .
                    ' `' .
                    $this->_left .
                    '`+2, `' .
                    $this->_left .
                    '`),' .
                    ' `' .
                    $this->_right .
                    '` = IF( `' .
                    $this->_right .
                    '`>= :right,' .
                    ' `' .
                    $this->_right .
                    '`+2, `' .
                    $this->_right .
                    '`)' .
                    ' WHERE `' .
                    $this->_right .
                    '` >= :right';

                $this->_db->query($sql, ['left' => $info[$this->_left], 'right' => $info[$this->_right]]);
                $this->_db->insert($this->_table, $data);
                $this->_db->commit();
            } catch (\PDOException $p) {
                $this->_db->rollBack();
                echo $p->getMessage();
                exit;
            } catch (\Exception $e) {
                $this->_db->rollBack();
                echo $e->getMessage();
                echo $sql;
                exit;
            }
            // TODO: change to ZEND LIBRARY
            $res = $this->_db->fetchOne('select last_insert_id()');
            return $res;
        }
        return false;
    }

    /**
     * @return array
     *
     * @deprecated Not used anymore.
     */
    public function checkNodes()
    {
        $sql = $this->_db->select();
        $sql->from(
            ['t1' => $this->_table],
            ['t1.' . $this->_id, new \Zend_Db_Expr('COUNT(t1.' . $this->_id . ') AS rep')]
        )->from(
            ['t2' => $this->_table]
        )->from(
            ['t3' => $this->_table],
            new \Zend_Db_Expr('MAX(t3.' . $this->_right . ') AS max_right')
        );

        $sql->where(
            't1.' . $this->_left . ' <> t2.' . $this->_left
        )->where(
            't1.' . $this->_left . ' <> t2.' . $this->_right
        )->where(
            't1.' . $this->_right . ' <> t2.' . $this->_right
        );

        $sql->group('t1.' . $this->_id);
        $sql->having('max_right <> SQRT(4 * rep + 1) + 1');
        return $this->_db->fetchAll($sql);
    }

    /**
     * @param string|int $nodeId
     * @return bool|Node|void
     *
     * @deprecated Not used anymore.
     */
    public function removeNode($nodeId)
    {
        $info = $this->getNodeInfo($nodeId);
        if (!$info) {
            return false;
        }

        if ($nodeId) {
            $this->_db->beginTransaction();
            try {
                /**
                 * DELETE FROM my_tree WHERE left_key >= $left_key AND right_key <= $right_key
                 */
                $this->_db->delete(
                    $this->_table,
                    $this->_left .
                    ' >= ' .
                    $info[$this->_left] .
                    ' AND ' .
                    $this->_right .
                    ' <= ' .
                    $info[$this->_right]
                );
                /**
                 * UPDATE my_tree SET left_key = IF(left_key > $left_key, left_key – ($right_key - $left_key + 1),
                 *      left_key), right_key = right_key – ($right_key - $left_key + 1) WHERE right_key > $right_key
                 */
                $sql = 'UPDATE ' .
                    $this->_table .
                    ' SET ' .
                    $this->_left .
                    ' = IF(' .
                    $this->_left .
                    ' > ' .
                    $info[$this->_left] .
                    ', ' .
                    $this->_left .
                    ' - ' .
                    ($info[$this->_right] -
                    $info[$this->_left] +
                    1) .
                    ', ' .
                    $this->_left .
                    '), ' .
                    $this->_right .
                    ' = ' .
                    $this->_right .
                    ' - ' .
                    ($info[$this->_right] -
                    $info[$this->_left] +
                    1) .
                    ' WHERE ' .
                    $this->_right .
                    ' > ' .
                    $info[$this->_right];
                $this->_db->query($sql);
                $this->_db->commit();
                return new Node($info, $this->getKeys());
            } catch (\Exception $e) {
                $this->_db->rollBack();
                echo $e->getMessage();
            }
        }
    }

    /**
     * @param string|int $eId
     * @param string|int $pId
     * @param string|int $aId
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @deprecated Not used anymore.
     */
    public function moveNode($eId, $pId, $aId = 0)
    {
        $eInfo = $this->getNodeInfo($eId);
        $pInfo = $this->getNodeInfo($pId);

        $leftId = $eInfo[$this->_left];
        $rightId = $eInfo[$this->_right];
        $level = $eInfo[$this->_level];

        $leftIdP = $pInfo[$this->_left];
        $rightIdP = $pInfo[$this->_right];
        $levelP = $pInfo[$this->_level];

        if ($eId == $pId ||
            $leftId == $leftIdP ||
            $leftIdP >= $leftId && $leftIdP <= $rightId ||
            $level == $levelP + 1 && $leftId > $leftIdP && $rightId < $rightIdP
        ) {
            echo "alert('cant_move_tree');";
            return false;
        }

        if ($leftIdP < $leftId && $rightIdP > $rightId && $levelP < $level - 1) {
            $sql = 'UPDATE ' .
                $this->_table .
                ' SET ' .
                $this->_level .
                ' = CASE WHEN ' .
                $this->_left .
                ' BETWEEN ' .
                $leftId .
                ' AND ' .
                $rightId .
                ' THEN ' .
                $this->_level .
                sprintf(
                    '%+d',
                    -($level - 1) + $levelP
                ) .
                ' ELSE ' .
                $this->_level .
                ' END, ' .
                $this->_right .
                ' = CASE WHEN ' .
                $this->_right .
                ' BETWEEN ' .
                ($rightId +
                1) .
                ' AND ' .
                ($rightIdP -
                1) .
                ' THEN ' .
                $this->_right .
                '-' .
                ($rightId -
                $leftId +
                1) .
                ' ' .
                'WHEN ' .
                $this->_left .
                ' BETWEEN ' .
                $leftId .
                ' AND ' .
                $rightId .
                ' THEN ' .
                $this->_right .
                '+' .
                (($rightIdP -
                $rightId -
                $level +
                $levelP) / 2 * 2 +
                $level -
                $levelP -
                1) .
                ' ELSE ' .
                $this->_right .
                ' END, ' .
                $this->_left .
                ' = CASE WHEN ' .
                $this->_left .
                ' BETWEEN ' .
                ($rightId +
                1) .
                ' AND ' .
                ($rightIdP -
                1) .
                ' THEN ' .
                $this->_left .
                '-' .
                ($rightId -
                $leftId +
                1) .
                ' ' .
                'WHEN ' .
                $this->_left .
                ' BETWEEN ' .
                $leftId .
                ' AND ' .
                $rightId .
                ' THEN ' .
                $this->_left .
                '+' .
                (($rightIdP -
                $rightId -
                $level +
                $levelP) / 2 * 2 +
                $level -
                $levelP -
                1) .
                ' ELSE ' .
                $this->_left .
                ' END ' .
                'WHERE ' .
                $this->_left .
                ' BETWEEN ' .
                ($leftIdP +
                1) .
                ' AND ' .
                ($rightIdP -
                1);
        } elseif ($leftIdP < $leftId) {
            $sql = 'UPDATE ' .
                $this->_table .
                ' SET ' .
                $this->_level .
                ' = CASE WHEN ' .
                $this->_left .
                ' BETWEEN ' .
                $leftId .
                ' AND ' .
                $rightId .
                ' THEN ' .
                $this->_level .
                sprintf(
                    '%+d',
                    -($level - 1) + $levelP
                ) .
                ' ELSE ' .
                $this->_level .
                ' END, ' .
                $this->_left .
                ' = CASE WHEN ' .
                $this->_left .
                ' BETWEEN ' .
                $rightIdP .
                ' AND ' .
                ($leftId -
                1) .
                ' THEN ' .
                $this->_left .
                '+' .
                ($rightId -
                $leftId +
                1) .
                ' ' .
                'WHEN ' .
                $this->_left .
                ' BETWEEN ' .
                $leftId .
                ' AND ' .
                $rightId .
                ' THEN ' .
                $this->_left .
                '-' .
                ($leftId -
                $rightIdP) .
                ' ELSE ' .
                $this->_left .
                ' END, ' .
                $this->_right .
                ' = CASE WHEN ' .
                $this->_right .
                ' BETWEEN ' .
                $rightIdP .
                ' AND ' .
                $leftId .
                ' THEN ' .
                $this->_right .
                '+' .
                ($rightId -
                $leftId +
                1) .
                ' ' .
                'WHEN ' .
                $this->_right .
                ' BETWEEN ' .
                $leftId .
                ' AND ' .
                $rightId .
                ' THEN ' .
                $this->_right .
                '-' .
                ($leftId -
                $rightIdP) .
                ' ELSE ' .
                $this->_right .
                ' END ' .
                'WHERE (' .
                $this->_left .
                ' BETWEEN ' .
                $leftIdP .
                ' AND ' .
                $rightId .
                ' ' .
                'OR ' .
                $this->_right .
                ' BETWEEN ' .
                $leftIdP .
                ' AND ' .
                $rightId .
                ')';
        } else {
            $sql = 'UPDATE ' .
                $this->_table .
                ' SET ' .
                $this->_level .
                ' = CASE WHEN ' .
                $this->_left .
                ' BETWEEN ' .
                $leftId .
                ' AND ' .
                $rightId .
                ' THEN ' .
                $this->_level .
                sprintf(
                    '%+d',
                    -($level - 1) + $levelP
                ) .
                ' ELSE ' .
                $this->_level .
                ' END, ' .
                $this->_left .
                ' = CASE WHEN ' .
                $this->_left .
                ' BETWEEN ' .
                $rightId .
                ' AND ' .
                $rightIdP .
                ' THEN ' .
                $this->_left .
                '-' .
                ($rightId -
                $leftId +
                1) .
                ' ' .
                'WHEN ' .
                $this->_left .
                ' BETWEEN ' .
                $leftId .
                ' AND ' .
                $rightId .
                ' THEN ' .
                $this->_left .
                '+' .
                ($rightIdP -
                1 -
                $rightId) .
                ' ELSE ' .
                $this->_left .
                ' END, ' .
                $this->_right .
                ' = CASE WHEN ' .
                $this->_right .
                ' BETWEEN ' .
                ($rightId +
                1) .
                ' AND ' .
                ($rightIdP -
                1) .
                ' THEN ' .
                $this->_right .
                '-' .
                ($rightId -
                $leftId +
                1) .
                ' ' .
                'WHEN ' .
                $this->_right .
                ' BETWEEN ' .
                $leftId .
                ' AND ' .
                $rightId .
                ' THEN ' .
                $this->_right .
                '+' .
                ($rightIdP -
                1 -
                $rightId) .
                ' ELSE ' .
                $this->_right .
                ' END ' .
                'WHERE (' .
                $this->_left .
                ' BETWEEN ' .
                $leftId .
                ' AND ' .
                $rightIdP .
                ' ' .
                'OR ' .
                $this->_right .
                ' BETWEEN ' .
                $leftId .
                ' AND ' .
                $rightIdP .
                ')';
        }

        $this->_db->beginTransaction();
        try {
            $this->_db->query($sql);
            $this->_db->commit();
            echo "alert('node moved');";
            return true;
        } catch (\Exception $e) {
            $this->_db->rollBack();
            echo "alert('node not moved: fatal error');";
            echo $e->getMessage();
            echo "<br>\r\n";
            echo $sql;
            echo "<br>\r\n";
            exit;
        }
    }

    /**
     * @param string|int $eId
     * @param string|int $pId
     * @param string|int $aId
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     *
     * @deprecated Not used anymore.
     */
    public function moveNodes($eId, $pId, $aId = 0)
    {
        $eInfo = $this->getNodeInfo($eId);
        if ($pId != 0) {
            $pInfo = $this->getNodeInfo($pId);
        }
        if ($aId != 0) {
            $aInfo = $this->getNodeInfo($aId);
        }

        $level = $eInfo[$this->_level];
        $leftKey = $eInfo[$this->_left];
        $rightKey = $eInfo[$this->_right];
        if ($pId == 0) {
            $levelUp = 0;
        } else {
            $levelUp = $pInfo[$this->_level];
        }

        $rightKeyNear = 0;
        $leftKeyNear = 0;

        if ($pId == 0) {
            //move to root
            $rightKeyNear = $this->_db->fetchOne('SELECT MAX(' . $this->_right . ') FROM ' . $this->_table);
        } elseif ($aId != 0 && $pId == $eInfo[$this->_pid]) {
            // if we have after ID
            $rightKeyNear = $aInfo[$this->_right];
            $leftKeyNear = $aInfo[$this->_left];
        } elseif ($aId == 0 && $pId == $eInfo[$this->_pid]) {
            // if we do not have after ID
            $rightKeyNear = $pInfo[$this->_left];
        } elseif ($pId != $eInfo[$this->_pid]) {
            $rightKeyNear = $pInfo[$this->_right] - 1;
        }

        $skewLevel = $pInfo[$this->_level] - $eInfo[$this->_level] + 1;
        $skewTree = $eInfo[$this->_right] - $eInfo[$this->_left] + 1;

        echo "alert('" . $rightKeyNear . "');";

        if ($rightKeyNear > $rightKey) {
            // up
            echo "alert('move up');";
            $skewEdit = $rightKeyNear - $leftKey + 1;
            $sql = 'UPDATE ' .
                $this->_table .
                ' SET ' .
                $this->_right .
                ' = IF(' .
                $this->_left .
                ' >= ' .
                $eInfo[$this->_left] .
                ', ' .
                $this->_right .
                ' + ' .
                $skewEdit .
                ', IF(' .
                $this->_right .
                ' < ' .
                $eInfo[$this->_left] .
                ', ' .
                $this->_right .
                ' + ' .
                $skewTree .
                ', ' .
                $this->_right .
                ')), ' .
                $this->_level .
                ' = IF(' .
                $this->_left .
                ' >= ' .
                $eInfo[$this->_left] .
                ', ' .
                $this->_level .
                ' + ' .
                $skewLevel .
                ', ' .
                $this->_level .
                '), ' .
                $this->_left .
                ' = IF(' .
                $this->_left .
                ' >= ' .
                $eInfo[$this->_left] .
                ', ' .
                $this->_left .
                ' + ' .
                $skewEdit .
                ', IF(' .
                $this->_left .
                ' > ' .
                $rightKeyNear .
                ', ' .
                $this->_left .
                ' + ' .
                $skewTree .
                ', ' .
                $this->_left .
                '))' .
                ' WHERE ' .
                $this->_right .
                ' > ' .
                $rightKeyNear .
                ' AND ' .
                $this->_left .
                ' < ' .
                $eInfo[$this->_right];
        } elseif ($rightKeyNear < $rightKey) {
            // down
            echo "alert('move down');";
            $skewEdit = $rightKeyNear - $leftKey + 1 - $skewTree;
            $sql = 'UPDATE ' .
                $this->_table .
                ' SET ' .
                $this->_left .
                ' = IF(' .
                $this->_right .
                ' <= ' .
                $rightKey .
                ', ' .
                $this->_left .
                ' + ' .
                $skewEdit .
                ', IF(' .
                $this->_left .
                ' > ' .
                $rightKey .
                ', ' .
                $this->_left .
                ' - ' .
                $skewTree .
                ', ' .
                $this->_left .
                ')), ' .
                $this->_level .
                ' = IF(' .
                $this->_right .
                ' <= ' .
                $rightKey .
                ', ' .
                $this->_level .
                ' + ' .
                $skewLevel .
                ', ' .
                $this->_level .
                '), ' .
                $this->_right .
                ' = IF(' .
                $this->_right .
                ' <= ' .
                $rightKey .
                ', ' .
                $this->_right .
                ' + ' .
                $skewEdit .
                ', IF(' .
                $this->_right .
                ' <= ' .
                $rightKeyNear .
                ', ' .
                $this->_right .
                ' - ' .
                $skewTree .
                ', ' .
                $this->_right .
                '))' .
                ' WHERE ' .
                $this->_right .
                ' > ' .
                $leftKey .
                ' AND ' .
                $this->_left .
                ' <= ' .
                $rightKeyNear;
        }

        $this->_db->beginTransaction();
        try {
            $this->_db->query($sql);
            $this->_db->commit();
        } catch (\Exception $e) {
            $this->_db->rollBack();
            echo $e->getMessage();
            echo "<br>\r\n";
            echo $sql;
            echo "<br>\r\n";
            exit;
        }
        echo "alert('node added')";
    }

    /**
     * @param string $tableName
     * @param string $joinCondition
     * @param string $fields
     * @return void
     *
     * @deprecated Not used anymore.
     */
    public function addTable($tableName, $joinCondition, $fields = '*')
    {
        $this->_extTables[$tableName] = ['joinCondition' => $joinCondition, 'fields' => $fields];
    }

    /**
     * @param Select $select
     * @return void
     *
     * @deprecated Not used anymore.
     */
    protected function _addExtTablesToSelect(Select &$select)
    {
        foreach ($this->_extTables as $tableName => $info) {
            $select->joinInner($tableName, $info['joinCondition'], $info['fields']);
        }
    }

    /**
     * @param string|int $nodeId
     * @param int $startLevel
     * @param int $endLevel
     * @return NodeSet
     *
     * @deprecated Not used anymore.
     */
    public function getChildren($nodeId, $startLevel = 0, $endLevel = 0)
    {
        try {
            $info = $this->getNodeInfo($nodeId);
        } catch (\Exception $e) {
            echo $e->getMessage();
            exit;
        }

        $dbSelect = new Select($this->_db);
        $dbSelect->from(
            $this->_table
        )->where(
            $this->_left . ' >= :left'
        )->where(
            $this->_right . ' <= :right'
        )->order(
            $this->_left
        );

        $this->_addExtTablesToSelect($dbSelect);

        $data = [];
        $data['left'] = $info[$this->_left];
        $data['right'] = $info[$this->_right];

        if (!empty($startLevel) && empty($endLevel)) {
            $dbSelect->where($this->_level . ' = :minLevel');
            $data['minLevel'] = $info[$this->_level] + $startLevel;
        }

        //echo $dbSelect->__toString();
        $data = $this->_db->fetchAll($dbSelect, $data);

        $nodeSet = new NodeSet();
        foreach ($data as $node) {
            $nodeSet->addNode(new Node($node, $this->getKeys()));
        }
        return $nodeSet;
    }

    /**
     * @param string|int $nodeId
     * @return Node
     *
     * @deprecated Not used anymore.
     */
    public function getNode($nodeId)
    {
        $dbSelect = new Select($this->_db);
        $dbSelect->from($this->_table)->where($this->_table . '.' . $this->_id . ' >= :id');

        $this->_addExtTablesToSelect($dbSelect);

        $data = [];
        $data['id'] = $nodeId;

        $data = $this->_db->fetchRow($dbSelect, $data);

        return new Node($data, $this->getKeys());
    }
}
