<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Tree;

use Magento\Framework\DB\Select;

/**
 * Data DB tree
 *
 * Data model:
 * id  |  path  |  order
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Dbp extends \Magento\Framework\Data\Tree
{
    const ID_FIELD = 'id';

    const PATH_FIELD = 'path';

    const ORDER_FIELD = 'order';

    const LEVEL_FIELD = 'level';

    /**
     * DB connection
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_conn;

    /**
     * Data table name
     *
     * @var string
     */
    protected $_table;

    /**
     * Indicates if loaded
     *
     * @var bool
     */
    protected $_loaded = false;

    /**
     * SQL select object
     *
     * @var \Magento\Framework\DB\Select
     */
    protected $_select;

    /**
     * Tree structure field: id
     *
     * @var string
     */
    protected $_idField;

    /**
     * Tree structure field: path
     *
     * @var string
     */
    protected $_pathField;

    /**
     * Tree structure field: order
     *
     * @var string
     */
    protected $_orderField;

    /**
     * Tree structure field: level
     *
     * @var string
     */
    protected $_levelField;

    /**
     * Db tree constructor
     *
     * $fields = array(
     *      \Magento\Framework\Data\Tree\Dbp::ID_FIELD       => string,
     *      \Magento\Framework\Data\Tree\Dbp::PATH_FIELD     => string,
     *      \Magento\Framework\Data\Tree\Dbp::ORDER_FIELD    => string
     *      \Magento\Framework\Data\Tree\Dbp::LEVEL_FIELD    => string
     * )
     *
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param string $table
     * @param array $fields
     * @throws \Exception
     */
    public function __construct(\Magento\Framework\DB\Adapter\AdapterInterface $connection, $table, $fields)
    {
        parent::__construct();

        if (!$connection) {
            throw new \Exception('Wrong "$connection" parametr');
        }

        $this->_conn = $connection;
        $this->_table = $table;

        if (!isset(
            $fields[self::ID_FIELD]
        ) || !isset(
            $fields[self::PATH_FIELD]
        ) || !isset(
            $fields[self::LEVEL_FIELD]
        ) || !isset(
            $fields[self::ORDER_FIELD]
        )
        ) {
            throw new \Exception('"$fields" tree configuratin array');
        }

        $this->_idField = $fields[self::ID_FIELD];
        $this->_pathField = $fields[self::PATH_FIELD];
        $this->_orderField = $fields[self::ORDER_FIELD];
        $this->_levelField = $fields[self::LEVEL_FIELD];

        $this->_select = $this->_conn->select();
        $this->_select->from($this->_table);
    }

    /**
     * Retrieve current select object
     *
     * @return Select
     */
    public function getDbSelect()
    {
        return $this->_select;
    }

    /**
     * Set Select object
     *
     * @param Select $select
     * @return void
     */
    public function setDbSelect($select)
    {
        $this->_select = $select;
    }

    /**
     * Load tree
     *
     * @param   int|Node|string $parentNode
     * @param   int $recursionLevel
     * @return  $this
     */
    public function load($parentNode = null, $recursionLevel = 0)
    {
        if (!$this->_loaded) {
            $startLevel = 1;
            $parentPath = '';

            if ($parentNode instanceof Node) {
                $parentPath = $parentNode->getData($this->_pathField);
                $startLevel = $parentNode->getData($this->_levelField);
            } elseif (is_numeric($parentNode)) {
                $select = $this->_conn->select()
                    ->from($this->_table, [$this->_pathField, $this->_levelField])
                    ->where("{$this->_idField} = ?", $parentNode);
                $parent = $this->_conn->fetchRow($select);

                $startLevel = $parent[$this->_levelField];
                $parentPath = $parent[$this->_pathField];
                $parentNode = null;
            } elseif (is_string($parentNode)) {
                $parentPath = $parentNode;
                $startLevel = count(explode(',', $parentPath)) - 1;
                $parentNode = null;
            }

            $select = clone $this->_select;

            $select->order($this->_table . '.' . $this->_orderField . ' ASC');
            if ($parentPath) {
                $pathField = $this->_conn->quoteIdentifier([$this->_table, $this->_pathField]);
                $select->where("{$pathField} LIKE ?", "{$parentPath}/%");
            }
            if ($recursionLevel != 0) {
                $levelField = $this->_conn->quoteIdentifier([$this->_table, $this->_levelField]);
                $select->where("{$levelField} <= ?", $startLevel + $recursionLevel);
            }

            $arrNodes = $this->_conn->fetchAll($select);

            $childrenItems = [];

            foreach ($arrNodes as $nodeInfo) {
                $pathToParent = explode('/', $nodeInfo[$this->_pathField]);
                array_pop($pathToParent);
                $pathToParent = implode('/', $pathToParent);
                $childrenItems[$pathToParent][] = $nodeInfo;
            }

            $this->addChildNodes($childrenItems, $parentPath, $parentNode);

            $this->_loaded = true;
        }

        return $this;
    }

    /**
     * Add child nodes
     *
     * @param array $children
     * @param string $path
     * @param Node $parentNode
     * @param int $level
     * @return void
     */
    public function addChildNodes($children, $path, $parentNode, $level = 0)
    {
        if (isset($children[$path])) {
            foreach ($children[$path] as $child) {
                $nodeId = isset($child[$this->_idField]) ? $child[$this->_idField] : false;
                if ($parentNode && $nodeId && ($node = $parentNode->getChildren()->searchById($nodeId))) {
                    $node->addData($child);
                } else {
                    $node = new Node($child, $this->_idField, $this, $parentNode);
                }

                //$node->setLevel(count(explode('/', $node->getData($this->_pathField)))-1);
                $node->setLevel($node->getData($this->_levelField));
                $node->setPathId($node->getData($this->_pathField));
                $this->addNode($node, $parentNode);

                if ($path) {
                    $childrenPath = explode('/', $path);
                } else {
                    $childrenPath = [];
                }
                $childrenPath[] = $node->getId();
                $childrenPath = implode('/', $childrenPath);

                $this->addChildNodes($children, $childrenPath, $node, $level + 1);
            }
        }
    }

    /**
     * Load node
     *
     * @param int|string $nodeId
     * @return Node
     */
    public function loadNode($nodeId)
    {
        $select = clone $this->_select;
        if (is_numeric($nodeId)) {
            $condField = $this->_conn->quoteIdentifier([$this->_table, $this->_idField]);
        } else {
            $condField = $this->_conn->quoteIdentifier([$this->_table, $this->_pathField]);
        }

        $select->where("{$condField} = ?", $nodeId);

        $node = new Node($this->_conn->fetchRow($select), $this->_idField, $this);
        $this->addNode($node);
        return $node;
    }

    /**
     * Get children
     *
     * @param Node $node
     * @param bool $recursive
     * @param array $result
     * @return array
     */
    public function getChildren($node, $recursive = true, $result = [])
    {
        if (is_numeric($node)) {
            $node = $this->getNodeById($node);
        }
        if (!$node) {
            return $result;
        }

        foreach ($node->getChildren() as $child) {
            if ($recursive) {
                if ($child->getChildren()) {
                    $result = $this->getChildren($child, $recursive, $result);
                }
            }
            $result[] = $child->getId();
        }
        return $result;
    }

    /**
     * Move tree node
     *
     * @param Node $node
     * @param Node $newParent
     * @param Node $prevNode
     * @return void
     * @throws \Exception
     * @todo Use adapter for generate conditions
     */
    public function move($node, $newParent, $prevNode = null)
    {
        $position = 1;

        $oldPath = $node->getData($this->_pathField);
        $newPath = $newParent->getData($this->_pathField);

        $newPath = $newPath . '/' . $node->getId();
        $oldPathLength = strlen($oldPath);

        $newLevel = $newParent->getLevel() + 1;
        $levelDisposition = $newLevel - $node->getLevel();

        $data = [
            $this->_levelField => new \Zend_Db_Expr("{$this->_levelField} + '{$levelDisposition}'"),
            $this->_pathField => new \Zend_Db_Expr(
                "CONCAT('{$newPath}', RIGHT({$this->_pathField}, LENGTH({$this->_pathField}) - {$oldPathLength}))"
            ),
        ];
        $condition = $this->_conn->quoteInto("{$this->_pathField} REGEXP ?", "^{$oldPath}(/|\$)");

        $this->_conn->beginTransaction();

        $reorderData = [$this->_orderField => new \Zend_Db_Expr("{$this->_orderField} + 1")];
        try {
            if ($prevNode && $prevNode->getId()) {
                $reorderCondition = "{$this->_orderField} > {$prevNode->getData($this->_orderField)}";
                $position = $prevNode->getData($this->_orderField) + 1;
            } else {
                $reorderCondition = $this->_conn->quoteInto(
                    "{$this->_pathField} REGEXP ?",
                    "^{$newParent->getData($this->_pathField)}/[0-9]+\$"
                );
                $select = $this->_conn->select()->from(
                    $this->_table,
                    new \Zend_Db_Expr("MIN({$this->_orderField})")
                )->where(
                    $reorderCondition
                );

                $position = (int)$this->_conn->fetchOne($select);
            }
            $this->_conn->update($this->_table, $reorderData, $reorderCondition);
            $this->_conn->update($this->_table, $data, $condition);
            $this->_conn->update(
                $this->_table,
                [$this->_orderField => $position, $this->_levelField => $newLevel],
                $this->_conn->quoteInto("{$this->_idField} = ?", $node->getId())
            );

            $this->_conn->commit();
        } catch (\Exception $e) {
            $this->_conn->rollBack();
            throw new \Exception("Can't move tree node due to error: " . $e->getMessage());
        }
    }

    /**
     * Load ensured nodes
     *
     * @param object $category
     * @param Node $rootNode
     * @return void
     */
    public function loadEnsuredNodes($category, $rootNode)
    {
        $pathIds = $category->getPathIds();
        $rootNodeId = $rootNode->getId();
        $rootNodePath = $rootNode->getData($this->_pathField);

        $select = clone $this->_select;
        $select->order($this->_table . '.' . $this->_orderField . ' ASC');

        if ($pathIds) {
            $condition = $this->_conn->quoteInto("{$this->_table}.{$this->_idField} in (?)", $pathIds);
            $select->where($condition);
        }

        $arrNodes = $this->_conn->fetchAll($select);

        if ($arrNodes) {
            $childrenItems = [];
            foreach ($arrNodes as $nodeInfo) {
                $nodeId = $nodeInfo[$this->_idField];
                if ($nodeId <= $rootNodeId) {
                    continue;
                }

                $pathToParent = explode('/', $nodeInfo[$this->_pathField]);
                array_pop($pathToParent);
                $pathToParent = implode('/', $pathToParent);
                $childrenItems[$pathToParent][] = $nodeInfo;
            }

            $this->_addChildNodes($childrenItems, $rootNodePath, $rootNode, true);
        }
    }

    /**
     * Add child nodes
     *
     * @param array $children
     * @param string $path
     * @param Node $parentNode
     * @param bool $withChildren
     * @param int $level
     * @return void
     */
    protected function _addChildNodes($children, $path, $parentNode, $withChildren = false, $level = 0)
    {
        if (isset($children[$path])) {
            foreach ($children[$path] as $child) {
                $nodeId = isset($child[$this->_idField]) ? $child[$this->_idField] : false;
                if ($parentNode && $nodeId && ($node = $parentNode->getChildren()->searchById($nodeId))) {
                    $node->addData($child);
                } else {
                    $node = new Node($child, $this->_idField, $this, $parentNode);
                    $node->setLevel($node->getData($this->_levelField));
                    $node->setPathId($node->getData($this->_pathField));
                    $this->addNode($node, $parentNode);
                }

                if ($withChildren) {
                    $this->_loaded = false;
                    $node->loadChildren(1);
                    $this->_loaded = false;
                }

                if ($path) {
                    $childrenPath = explode('/', $path);
                } else {
                    $childrenPath = [];
                }
                $childrenPath[] = $node->getId();
                $childrenPath = implode('/', $childrenPath);

                $this->_addChildNodes($children, $childrenPath, $node, $withChildren, $level + 1);
            }
        }
    }
}
