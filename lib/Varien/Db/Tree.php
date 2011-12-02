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
 * @copyright  Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Varien Library
 *
 *
 * @category   Varien
 * @package    Varien_Db
 * @author      Magento Core Team <core@magentocommerce.com>
 */


require_once 'Varien/Db/Tree/Exception.php';
Zend_Loader::loadClass('Zend_Db_Select');
Zend_Loader::loadClass('Varien_Db_Tree_Node');
Zend_Loader::loadClass('Varien_Db_Tree_NodeSet');

class Varien_Db_Tree
{

    private $_id;
    private $_left;
    private $_right;
    private $_level;
    private $_pid;
    private $_nodesInfo = array();

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
    private $_extTables = array();

    /**
     * Zend_Db_Adapter
     *
     * @var Zend_Db_Adapter_Abstract
     */
    private $_db;

    private $_table;

    function __construct($config = array())
    {
        // set a Zend_Db_Adapter connection
        if (! empty($config['db'])) {

            // convenience variable
            $db = $config['db'];

            // use an object from the registry?
            if (is_string($db)) {
                $db = Zend::registry($db);
            }

            // make sure it's a Zend_Db_Adapter
            if (! $db instanceof Zend_Db_Adapter_Abstract) {
                throw new Varien_Db_Tree_Exception('db object does not implement Zend_Db_Adapter_Abstract');
            }

            // save the connection
            $this->_db = $db;
            $conn = $this->_db->getConnection();
            if ($conn instanceof PDO) {
                $conn->setAttribute (PDO::ATTR_EMULATE_PREPARES, true);
            } elseif ($conn instanceof mysqli) {
                //TODO: ???
            }
        } else {
            throw new Varien_Db_Tree_Exception('db object is not set in config');
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
     * @return Varien_Db_Tree
     */
    public function setIdField($name) {
        $this->_id = $name;
        return $this;
    }

    /**
     * set name of left field
     *
     * @param string $name
     * @return Varien_Db_Tree
     */
    public function setLeftField($name) {
        $this->_left = $name;
        return $this;
    }

    /**
     * set name of right field
     *
     * @param string $name
     * @return Varien_Db_Tree
     */
    public function setRightField($name) {
        $this->_right = $name;
        return $this;
    }

    /**
     * set name of level field
     *
     * @param string $name
     * @return Varien_Db_Tree
     */
    public function setLevelField($name) {
        $this->_level = $name;
        return $this;
    }

    /**
     * set name of pid Field
     *
     * @param string $name
     * @return Varien_Db_Tree
     */
    public function setPidField($name) {
        $this->_pid = $name;
        return $this;
    }

    /**
     * set table name
     *
     * @param string $name
     * @return Varien_Db_Tree
     */
    public function setTable($name) {
        $this->_table = $name;
        return $this;
    }

    public function getKeys() {
        $keys = array();
        $keys['id'] = $this->_id;
        $keys['left'] = $this->_left;
        $keys['right'] = $this->_right;
        $keys['pid'] = $this->_pid;
        $keys['level'] = $this->_level;
        return $keys;
    }

    /**
     * Cleare table and add root element
     *
     */
    public function clear($data = array())
    {
        // clearing table
        $this->_db->query('TRUNCATE '. $this->_table);
        //$this->_db->delete($this->_table,'');

        // prepare data for root element
        $data[$this->_pid] = 0;
        $data[$this->_left] = 1;
        $data[$this->_right] = 2;
        $data[$this->_level] = 0;

        try  {
            $this->_db->insert($this->_table, $data);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
        return $this->_db->lastInsertId();
    }

    public function getNodeInfo($ID) {
        if (empty($this->_nodesInfo[$ID])) {
            $sql = 'SELECT * FROM '.$this->_table.' WHERE '.$this->_id.'=:id';
            $res = $this->_db->query($sql, array('id' => $ID));
            $data = $res->fetch();
            $this->_nodesInfo[$ID] = $data;
        } else {
            $data = $this->_nodesInfo[$ID];
        }
        return $data;
    }

    public function appendChild($ID, $data) {

        if (!$info = $this->getNodeInfo($ID)) {
            return false;
        }

        $data[$this->_left]  = $info[$this->_right];
        $data[$this->_right] = $info[$this->_right] + 1;
        $data[$this->_level] = $info[$this->_level] + 1;
        $data[$this->_pid]   = $ID;

        // creating a place for the record being inserted
        if($ID) {
            $this->_db->beginTransaction();
            try {
                $sql = 'UPDATE '.$this->_table.' SET'
                    . ' `'.$this->_left.'` = IF( `'.$this->_left.'` > :left, `'.$this->_left.'`+2, `'.$this->_left.'`),'
                    . ' `'.$this->_right.'` = IF( `'.$this->_right.'`>= :right, `'.$this->_right.'`+2, `'.$this->_right.'`)'
                    . ' WHERE `'.$this->_right.'` >= :right';

                $this->_db->query($sql, array('left'=>$info[$this->_left], 'right'=>$info[$this->_right]));

                $this->_db->insert($this->_table, $data);
                $this->_db->commit();
            } catch (PDOException $p) {
                $this->_db->rollBack();
                echo $p->getMessage();
                exit();
            } catch (Exception $e) {
                $this->_db->rollBack();
                echo $e->getMessage();
                echo $sql;
                var_dump($data);
                exit();
            }
            // TODO: change to ZEND LIBRARY
            $res =  $this->_db->fetchOne('select last_insert_id()');
            return $res;
           //return $this->_db->fetchOne('select last_insert_id()');
            //return $this->_db->lastInsertId();
        }
        return  false;
    }

    public function checkNodes() {
        $sql = $this->_db->select();

        $sql->from(array('t1'=>$this->_table), array('t1.'.$this->_id, new Zend_Db_Expr('COUNT(t1.'.$this->_id.') AS rep')))
        ->from(array('t2'=>$this->_table))
        ->from(array('t3'=>$this->_table), new Zend_Db_Expr('MAX(t3.'.$this->_right.') AS max_right'));


        $sql->where('t1.'.$this->_left.' <> t2.'.$this->_left)
        ->where('t1.'.$this->_left.' <> t2.'.$this->_right)
        ->where('t1.'.$this->_right.' <> t2.'.$this->_right);

        $sql->group('t1.'.$this->_id);
        $sql->having('max_right <> SQRT(4 * rep + 1) + 1');


        return $this->_db->fetchAll($sql);
    }

    public function insertBefore($ID, $data) {

    }

    public function removeNode($ID) {

        if (!$info = $this->getNodeInfo($ID)) {
            return false;
        }

        if($ID) {
            $this->_db->beginTransaction();
            try {
                // DELETE FROM my_tree WHERE left_key >= $left_key AND right_key <= $right_key
                $this->_db->delete($this->_table, $this->_left.' >= '.$info[$this->_left].' AND '.$this->_right.' <= '.$info[$this->_right]);

                // UPDATE my_tree SET left_key = IF(left_key > $left_key, left_key – ($right_key - $left_key + 1), left_key), right_key = right_key – ($right_key - $left_key + 1) WHERE right_key > $right_key
                $sql = 'UPDATE '.$this->_table.'
					SET
						'.$this->_left.' = IF('.$this->_left.' > '.$info[$this->_left].', '.$this->_left.' - '.($info[$this->_right] - $info[$this->_left] + 1).', '.$this->_left.'),
						'.$this->_right.' = '.$this->_right.' - '.($info[$this->_right] - $info[$this->_left] + 1).'
					WHERE
						'.$this->_right.' > '.$info[$this->_right];
                $this->_db->query($sql);
                $this->_db->commit();
                return new Varien_Db_Tree_Node($info, $this->getKeys());;
            } catch (Exception $e) {
                $this->_db->rollBack();
                echo $e->getMessage();
            }
        }
    }


    public function moveNode($eId, $pId, $aId = 0) {

        $eInfo = $this->getNodeInfo($eId);
        $pInfo = $this->getNodeInfo($pId);


        $leftId = $eInfo[$this->_left];
        $rightId = $eInfo[$this->_right];
        $level = $eInfo[$this->_level];

        $leftIdP = $pInfo[$this->_left];
        $rightIdP = $pInfo[$this->_right];
        $levelP = $pInfo[$this->_level];

        if ($eId == $pId || $leftId == $leftIdP || ($leftIdP >= $leftId && $leftIdP <= $rightId) || ($level == $levelP+1 && $leftId > $leftIdP && $rightId < $rightIdP)) {
            echo "alert('cant_move_tree');";
            return FALSE;
        }

        if ($leftIdP < $leftId && $rightIdP > $rightId && $levelP < $level - 1) {
            $sql = 'UPDATE '.$this->_table.' SET '
            . $this->_level . ' = CASE WHEN ' . $this->_left . ' BETWEEN ' . $leftId . ' AND ' . $rightId . ' THEN ' . $this->_level.sprintf('%+d', -($level-1)+$levelP) . ' ELSE ' . $this->_level . ' END, '
            . $this->_right . ' = CASE WHEN ' . $this->_right . ' BETWEEN ' . ($rightId+1) . ' AND ' . ($rightIdP-1) . ' THEN ' . $this->_right . '-' . ($rightId-$leftId+1) . ' '
            . 'WHEN ' . $this->_left . ' BETWEEN ' . $leftId . ' AND ' . $rightId . ' THEN ' . $this->_right . '+' . ((($rightIdP-$rightId-$level+$levelP)/2)*2+$level-$levelP-1) . ' ELSE ' . $this->_right . ' END, '
            . $this->_left . ' = CASE WHEN ' . $this->_left . ' BETWEEN ' . ($rightId+1) . ' AND ' . ($rightIdP-1) . ' THEN ' . $this->_left . '-' . ($rightId-$leftId+1) . ' '
            . 'WHEN ' . $this->_left . ' BETWEEN ' . $leftId . ' AND ' . $rightId . ' THEN ' . $this->_left . '+' . ((($rightIdP-$rightId-$level+$levelP)/2)*2+$level-$levelP-1) . ' ELSE ' . $this->_left . ' END '
            . 'WHERE ' . $this->_left . ' BETWEEN ' . ($leftIdP+1) . ' AND ' . ($rightIdP-1);
        } elseif ($leftIdP < $leftId) {
            $sql = 'UPDATE ' . $this->_table . ' SET '
            . $this->_level . ' = CASE WHEN ' . $this->_left . ' BETWEEN ' . $leftId . ' AND ' . $rightId . ' THEN ' . $this->_level.sprintf('%+d', -($level-1)+$levelP) . ' ELSE ' . $this->_level . ' END, '
            . $this->_left . ' = CASE WHEN ' . $this->_left . ' BETWEEN ' . $rightIdP . ' AND ' . ($leftId-1) . ' THEN ' . $this->_left . '+' . ($rightId-$leftId+1) . ' '
            . 'WHEN ' . $this->_left . ' BETWEEN ' . $leftId . ' AND ' . $rightId . ' THEN ' . $this->_left . '-' . ($leftId-$rightIdP) . ' ELSE ' . $this->_left . ' END, '
            . $this->_right . ' = CASE WHEN ' . $this->_right . ' BETWEEN ' . $rightIdP . ' AND ' . $leftId . ' THEN ' . $this->_right . '+' . ($rightId-$leftId+1) . ' '
            . 'WHEN ' . $this->_right . ' BETWEEN ' . $leftId . ' AND ' . $rightId . ' THEN ' . $this->_right . '-' . ($leftId-$rightIdP) . ' ELSE ' . $this->_right . ' END '
            . 'WHERE (' . $this->_left . ' BETWEEN ' . $leftIdP . ' AND ' . $rightId. ' '
            . 'OR ' . $this->_right . ' BETWEEN ' . $leftIdP . ' AND ' . $rightId . ')';
        } else {
            $sql = 'UPDATE ' . $this->_table . ' SET '
            . $this->_level . ' = CASE WHEN ' . $this->_left . ' BETWEEN ' . $leftId . ' AND ' . $rightId . ' THEN ' . $this->_level.sprintf('%+d', -($level-1)+$levelP) . ' ELSE ' . $this->_level . ' END, '
            . $this->_left . ' = CASE WHEN ' . $this->_left . ' BETWEEN ' . $rightId . ' AND ' . $rightIdP . ' THEN ' . $this->_left . '-' . ($rightId-$leftId+1) . ' '
            . 'WHEN ' . $this->_left . ' BETWEEN ' . $leftId . ' AND ' . $rightId . ' THEN ' . $this->_left . '+' . ($rightIdP-1-$rightId) . ' ELSE ' . $this->_left . ' END, '
            . $this->_right . ' = CASE WHEN ' . $this->_right . ' BETWEEN ' . ($rightId+1) . ' AND ' . ($rightIdP-1) . ' THEN ' . $this->_right . '-' . ($rightId-$leftId+1) . ' '
            . 'WHEN ' . $this->_right . ' BETWEEN ' . $leftId . ' AND ' . $rightId . ' THEN ' . $this->_right . '+' . ($rightIdP-1-$rightId) . ' ELSE ' . $this->_right . ' END '
            . 'WHERE (' . $this->_left . ' BETWEEN ' . $leftId . ' AND ' . $rightIdP . ' '
            . 'OR ' . $this->_right . ' BETWEEN ' . $leftId . ' AND ' . $rightIdP . ')';
        }

        $this->_db->beginTransaction();
        try {
            $this->_db->query($sql);
            $this->_db->commit();
            echo "alert('node moved');";
            return true;
        } catch (Exception $e) {
            $this->_db->rollBack();
            echo "alert('node not moved: fatal error');";
            echo $e->getMessage();
            echo "<br>\r\n";
            echo $sql;
            echo "<br>\r\n";
            exit();
        }
    }


    public function __moveNode($eId, $pId, $aId = 0) {

        $eInfo = $this->getNodeInfo($eId);
        if ($pId != 0) {
            $pInfo = $this->getNodeInfo($pId);
        }
        if ($aId != 0) {
            $aInfo = $this->getNodeInfo($aId);
        }

        $level = $eInfo[$this->_level];
        $left_key = $eInfo[$this->_left];
        $right_key = $eInfo[$this->_right];
        if ($pId == 0) {
            $level_up = 0;
        } else {
            $level_up = $pInfo[$this->_level];
        }

        $right_key_near = 0;
        $left_key_near = 0;

        if ($pId == 0) { //move to root
            $right_key_near = $this->_db->fetchOne('SELECT MAX('.$this->_right.') FROM '.$this->_table);
        } elseif ($aId != 0 && $pID == $eInfo[$this->_pid]) { // if we have after ID
            $right_key_near = $aInfo[$this->_right];
            $left_key_near = $aInfo[$this->_left];
        } elseif ($aId == 0 && $pId == $eInfo[$this->_pid]) { // if we do not have after ID
            $right_key_near = $pInfo[$this->_left];
        } elseif ($pId != $eInfo[$this->_pid]) {
            $right_key_near = $pInfo[$this->_right] - 1;
        }


        $skew_level = $pInfo[$this->_level] - $eInfo[$this->_level] + 1;
        $skew_tree = $eInfo[$this->_right] - $eInfo[$this->_left] + 1;

        echo "alert('".$right_key_near."');";

        if ($right_key_near > $right_key) { // up
            echo "alert('move up');";
            $skew_edit = $right_key_near - $left_key + 1;
            $sql = 'UPDATE '.$this->_table.'
                SET
                '.$this->_right.' = IF('.$this->_left.' >= '.$eInfo[$this->_left].', '.$this->_right.' + '.$skew_edit.', IF('.$this->_right.' < '.$eInfo[$this->_left].', '.$this->_right.' + '.$skew_tree.', '.$this->_right.')),
                '.$this->_level.' = IF('.$this->_left.' >= '.$eInfo[$this->_left].', '.$this->_level.' + '.$skew_level.', '.$this->_level.'),
                '.$this->_left.' = IF('.$this->_left.' >= '.$eInfo[$this->_left].', '.$this->_left.' + '.$skew_edit.', IF('.$this->_left.' > '.$right_key_near.', '.$this->_left.' + '.$skew_tree.', '.$this->_left.'))
                WHERE '.$this->_right.' > '.$right_key_near.' AND '.$this->_left.' < '.$eInfo[$this->_right];
        } elseif ($right_key_near < $right_key) { // down
            echo "alert('move down');";
            $skew_edit = $right_key_near - $left_key + 1 - $skew_tree;
            $sql = 'UPDATE '.$this->_table.'
                SET
                    '.$this->_left.' = IF('.$this->_right.' <= '.$right_key.', '.$this->_left.' + '.$skew_edit.', IF('.$this->_left.' > '.$right_key.', '.$this->_left.' - '.$skew_tree.', '.$this->_left.')),
                    '.$this->_level.' = IF('.$this->_right.' <= '.$right_key.', '.$this->_level.' + '.$skew_level.', '.$this->_level.'),
                    '.$this->_right.' = IF('.$this->_right.' <= '.$right_key.', '.$this->_right.' + '.$skew_edit.', IF('.$this->_right.' <= '.$right_key_near.', '.$this->_right.' - '.$skew_tree.', '.$this->_right.'))
                WHERE
                    '.$this->_right.' > '.$left_key.' AND '.$this->_left.' <= '.$right_key_near;
        }


        $this->_db->beginTransaction();
        try {
           $this->_db->query($sql);
           //$afrows = $this->_db->get
           $this->_db->commit();

        } catch (Exception $e) {
            $this->_db->rollBack();
            echo $e->getMessage();
            echo "<br>\r\n";
            echo $sql;
            echo "<br>\r\n";
            exit();
        }
        echo "alert('node added')";
    }

    public function addTable($tableName, $joinCondition, $fields='*')
    {
        $this->_extTables[$tableName] = array(
           'joinCondition' => $joinCondition,
           'fields'        => $fields
        );
    }

    protected function _addExtTablesToSelect(Zend_Db_Select &$select)
    {
        foreach ($this->_extTables as $tableName=>$info) {
            $select->joinInner($tableName, $info['joinCondition'], $info['fields']);
        }
    }

    public function getChildren($ID, $start_level = 0, $end_level = 0)
    {
        try {
            $info = $this->getNodeInfo($ID);
        } catch (Exception $e) {
            echo $e->getMessage();
            exit;
        }

        $dbSelect = new Zend_Db_Select($this->_db);
        $dbSelect->from($this->_table)
            ->where($this->_left  . ' >= :left')
            ->where($this->_right . ' <= :right')
            ->order($this->_left);

        $this->_addExtTablesToSelect($dbSelect);

        $data = array();
        $data['left'] = $info[$this->_left];
        $data['right'] = $info[$this->_right];

        if (!empty($start_level) && empty($end_level)) {
            $dbSelect->where($this->_level . ' = :minLevel');
            $data['minLevel'] = $info[$this->_level] + $start_level;
        }

        //echo $dbSelect->__toString();
        $data = $this->_db->fetchAll($dbSelect, $data);

        $nodeSet = new Varien_Db_Tree_NodeSet();
        foreach ($data as $node) {
             $nodeSet->addNode(new Varien_Db_Tree_Node($node, $this->getKeys()));
        }
        return $nodeSet;
    }

    public function getNode($nodeId)
    {
        $dbSelect = new Zend_Db_Select($this->_db);
        $dbSelect->from($this->_table)
            ->where($this->_table.'.'.$this->_id  . ' >= :id');

        $this->_addExtTablesToSelect($dbSelect);

        $data = array();
        $data['id'] = $nodeId;

        $data = $this->_db->fetchRow($dbSelect, $data);

        return new Varien_Db_Tree_Node($data, $this->getKeys());
    }
}
