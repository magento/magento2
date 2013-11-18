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
 * @category   Magento
 * @package    Magento_Data
 * @copyright  Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Data DB tree
 *
 * Data model:
 * id  |  pid  |  level | order
 *
 * @category   Magento
 * @package    Magento_Data
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Data\Tree;

class Db extends \Magento\Data\Tree
{
    const ID_FIELD      = 'id';
    const PARENT_FIELD  = 'parent';
    const LEVEL_FIELD   = 'level';
    const ORDER_FIELD   = 'order';

    /**
     * DB connection
     *
     * @var \Zend_Db_Adapter_Abstract
     */
    protected $_conn;

    /**
     * Data table name
     *
     * @var string
     */
    protected $_table;

    /**
     * SQL select object
     *
     * @var \Zend_Db_Select
     */
    protected $_select;

    /**
     * Tree ctructure field names
     *
     * @var string
     */
    protected $_idField;
    protected $_parentField;
    protected $_levelField;
    protected $_orderField;

    /**
     * Db tree constructor
     *
     * $fields = array(
     *      \Magento\Data\Tree\Db::ID_FIELD       => string,
     *      \Magento\Data\Tree\Db::PARENT_FIELD   => string,
     *      \Magento\Data\Tree\Db::LEVEL_FIELD    => string
     *      \Magento\Data\Tree\Db::ORDER_FIELD    => string
     * )
     *
     * @param \Zend_Db_Adapter_Abstract $connection
     * @param string $table
     * @param array $fields
     */
    public function __construct($connection, $table, $fields)
    {
        parent::__construct();

        if (!$connection) {
            throw new \Exception('Wrong "$connection" parametr');
        }

        $this->_conn    = $connection;
        $this->_table   = $table;

        if (!isset($fields[self::ID_FIELD]) ||
            !isset($fields[self::PARENT_FIELD]) ||
            !isset($fields[self::LEVEL_FIELD]) ||
            !isset($fields[self::ORDER_FIELD])) {

            throw new \Exception('"$fields" tree configuratin array');
        }

        $this->_idField     = $fields[self::ID_FIELD];
        $this->_parentField = $fields[self::PARENT_FIELD];
        $this->_levelField  = $fields[self::LEVEL_FIELD];
        $this->_orderField  = $fields[self::ORDER_FIELD];

        $this->_select  = $this->_conn->select();
        $this->_select->from($this->_table, array_values($fields));
    }

    public function getDbSelect()
    {
        return $this->_select;
    }

    public function setDbSelect($select)
    {
        $this->_select = $select;
    }

    /**
     * Load tree
     *
     * @param   int || \Magento\Data\Tree\Node $parentNode
     * @param   int $recursionLevel recursion level
     * @return  this
     */
    public function load($parentNode=null, $recursionLevel=100)
    {
        if (is_null($parentNode)) {
            $this->_loadFullTree();
            return $this;
        }
        elseif ($parentNode instanceof \Magento\Data\Tree\Node) {
            $parentId = $parentNode->getId();
        }
        elseif (is_numeric($parentNode)) {
            $parentId = $parentNode;
            $parentNode = null;
        }
        else {
            throw new \Exception('root node id is not defined');
        }

        $select = clone $this->_select;
        $select->order($this->_table.'.'.$this->_orderField . ' ASC');
        $condition = $this->_conn->quoteInto("$this->_table.$this->_parentField=?", $parentId);
        $select->where($condition);
        $arrNodes = $this->_conn->fetchAll($select);
        foreach ($arrNodes as $nodeInfo) {
            $node = new \Magento\Data\Tree\Node($nodeInfo, $this->_idField, $this, $parentNode);
            $this->addNode($node, $parentNode);

            if ($recursionLevel) {
                $node->loadChildren($recursionLevel-1);
            }
        }
        return $this;
    }

    public function loadNode($nodeId)
    {
        $select = clone $this->_select;
        $condition = $this->_conn->quoteInto("$this->_table.$this->_idField=?", $nodeId);
        $select->where($condition);
        $node = new \Magento\Data\Tree\Node($this->_conn->fetchRow($select), $this->_idField, $this);
        $this->addNode($node);
        return $node;
    }

    public function appendChild($data=array(), $parentNode, $prevNode=null)
    {
        $orderSelect = $this->_conn->select();
        $orderSelect->from($this->_table, new \Zend_Db_Expr('MAX('.$this->_conn->quoteIdentifier($this->_orderField).')'))
            ->where($this->_conn->quoteIdentifier($this->_parentField).'='.$parentNode->getId());

        $order = $this->_conn->fetchOne($orderSelect);
        $data[$this->_parentField] = $parentNode->getId();
        $data[$this->_levelField]  = $parentNode->getData($this->_levelField)+1;
        $data[$this->_orderField]  = $order+1;

        $this->_conn->insert($this->_table, $data);
        $data[$this->_idField] = $this->_conn->lastInsertId();

        return parent::appendChild($data, $parentNode, $prevNode);
    }

    /**
     * Move tree node
     *
     * @param \Magento\Data\Tree\Node $node
     * @param \Magento\Data\Tree\Node $parentNode
     * @param \Magento\Data\Tree\Node $prevNode
     */
    public function moveNodeTo($node, $parentNode, $prevNode=null)
    {
        $data = array();
        $data[$this->_parentField]  = $parentNode->getId();
        $data[$this->_levelField]   = $parentNode->getData($this->_levelField)+1;
        // New node order
        if (is_null($prevNode) || is_null($prevNode->getData($this->_orderField))) {
            $data[$this->_orderField] = 1;
        }
        else {
            $data[$this->_orderField] = $prevNode->getData($this->_orderField)+1;
        }
        $condition = $this->_conn->quoteInto("$this->_idField=?", $node->getId());

        // For reorder new node branch
        $dataReorderNew = array(
            $this->_orderField => new \Zend_Db_Expr($this->_conn->quoteIdentifier($this->_orderField).'+1')
        );
        $conditionReorderNew = $this->_conn->quoteIdentifier($this->_parentField).'='.$parentNode->getId().
                            ' AND '.$this->_conn->quoteIdentifier($this->_orderField).'>='. $data[$this->_orderField];

        // For reorder old node branch
        $dataReorderOld = array(
            $this->_orderField => new \Zend_Db_Expr($this->_conn->quoteIdentifier($this->_orderField).'-1')
        );
        $conditionReorderOld = $this->_conn->quoteIdentifier($this->_parentField).'='.$node->getData($this->_parentField).
                            ' AND '.$this->_conn->quoteIdentifier($this->_orderField).'>'.$node->getData($this->_orderField);

        $this->_conn->beginTransaction();
        try {
            // Prepare new node branch
            $this->_conn->update($this->_table, $dataReorderNew, $conditionReorderNew);
            // Move node
            $this->_conn->update($this->_table, $data, $condition);
            // Update old node branch
            $this->_conn->update($this->_table, $dataReorderOld, $conditionReorderOld);
            $this->_updateChildLevels($node->getId(), $data[$this->_levelField]);
            $this->_conn->commit();
        }
        catch (\Exception $e){
            $this->_conn->rollBack();
            throw new \Exception('Can\'t move tree node');
        }
    }

    protected function _updateChildLevels($parentId, $parentLevel)
    {
        $select = $this->_conn->select()
            ->from($this->_table, $this->_idField)
            ->where($this->_parentField.'=?', $parentId);
        $ids = $this->_conn->fetchCol($select);

        if (!empty($ids)) {
            $this->_conn->update($this->_table,
                array($this->_levelField=>$parentLevel+1),
                $this->_conn->quoteInto($this->_idField.' IN (?)', $ids));
            foreach ($ids as $id) {
            	$this->_updateChildLevels($id, $parentLevel+1);
            }
        }
        return $this;
    }

    protected function _loadFullTree()
    {
        $select = clone $this->_select;
        $select->order($this->_table . '.' . $this->_levelField)
            ->order($this->_table.'.'.$this->_orderField);

        $arrNodes = $this->_conn->fetchAll($select);

        foreach ($arrNodes as $nodeInfo) {
            $node = new \Magento\Data\Tree\Node($nodeInfo, $this->_idField, $this);
            $parentNode = $this->getNodeById($nodeInfo[$this->_parentField]);
            $this->addNode($node, $parentNode);
        }

        return $this;
    }

    public function removeNode($node)
    {
        // For reorder old node branch
        $dataReorderOld = array(
            $this->_orderField => new \Zend_Db_Expr($this->_conn->quoteIdentifier($this->_orderField).'-1')
        );
        $conditionReorderOld = $this->_conn->quoteIdentifier($this->_parentField).'='.$node->getData($this->_parentField).
                            ' AND '.$this->_conn->quoteIdentifier($this->_orderField).'>'.$node->getData($this->_orderField);

        $this->_conn->beginTransaction();
        try {
            $condition = $this->_conn->quoteInto("$this->_idField=?", $node->getId());
            $this->_conn->delete($this->_table, $condition);
            // Update old node branch
            $this->_conn->update($this->_table, $dataReorderOld, $conditionReorderOld);
            $this->_conn->commit();
        }
        catch (\Exception $e){
            $this->_conn->rollBack();
            throw new \Exception('Can\'t remove tree node');
        }
        parent::removeNode($node);
        return $this;
    }
}
