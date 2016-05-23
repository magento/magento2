<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Tree;

use Magento\Framework\Exception\LocalizedException;

/**
 * @SuppressWarnings(PHPMD.UnusedPrivateField)
 */
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
     * @throws LocalizedException
     */
    public function __construct($nodeData, $keys)
    {
        if (empty($nodeData)) {
            throw new LocalizedException(new \Magento\Framework\Phrase('Empty array of node information'));
        }
        if (empty($keys)) {
            throw new LocalizedException(new \Magento\Framework\Phrase('Empty keys array'));
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
