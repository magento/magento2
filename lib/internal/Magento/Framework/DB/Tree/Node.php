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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\DB\Tree;

use Magento\Framework\DB\Tree\Node\NodeException;

class Node
{
    /**
     * @var int
     */
    private $left;

    /**
     * @var int
     */
    private $right;

    /**
     * @var string|int
     */
    private $id;

    /**
     * @var string|int
     */
    private $pid;

    /**
     * @var int
     */
    private $level;

    /**
     * @var string
     */
    private $title;

    /**
     * @var array
     */
    private $data;

    /**
     * @var bool
     */
    public $hasChild = false;

    /**
     * @var float|int
     */
    public $numChild = 0;

    /**
     * @param array $nodeData
     * @param array $keys
     * @throws NodeException
     */
    public function __construct($nodeData, $keys)
    {
        if (empty($nodeData)) {
            throw new NodeException('Empty array of node information');
        }
        if (empty($keys)) {
            throw new NodeException('Empty keys array');
        }

        $this->id = $nodeData[$keys['id']];
        $this->pid = $nodeData[$keys['pid']];
        $this->left = $nodeData[$keys['left']];
        $this->right = $nodeData[$keys['right']];
        $this->level = $nodeData[$keys['level']];

        $this->data = $nodeData;
        $a = $this->right - $this->left;
        if ($a > 1) {
            $this->hasChild = true;
            $this->numChild = ($a - 1) / 2;
        }
        return $this;
    }

    /**
     * @param string $name
     * @return null|array
     */
    public function getData($name)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        } else {
            return null;
        }
    }

    /**
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @return int
     */
    public function getLeft()
    {
        return $this->left;
    }

    /**
     * @return int
     */
    public function getRight()
    {
        return $this->right;
    }

    /**
     * @return string|int
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * @return string|int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Return true if node has child
     *
     * @return bool
     */
    public function isParent()
    {
        if ($this->right - $this->left > 1) {
            return true;
        } else {
            return false;
        }
    }
}
