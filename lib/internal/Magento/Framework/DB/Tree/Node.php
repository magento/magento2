<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Tree;

use Magento\Framework\Exception\LocalizedException;

/**
 * @SuppressWarnings(PHPMD.UnusedPrivateField)
 * @since 2.0.0
 */
class Node
{
    /**
     * @var int
     * @since 2.0.0
     */
    private $left;

    /**
     * @var int
     * @since 2.0.0
     */
    private $right;

    /**
     * @var string|int
     * @since 2.0.0
     */
    private $id;

    /**
     * @var string|int
     * @since 2.0.0
     */
    private $pid;

    /**
     * @var int
     * @since 2.0.0
     */
    private $level;

    /**
     * @var string
     * @since 2.0.0
     */
    private $title;

    /**
     * @var array
     * @since 2.0.0
     */
    private $data;

    /**
     * @var bool
     * @since 2.0.0
     */
    public $hasChild = false;

    /**
     * @var float|int
     * @since 2.0.0
     */
    public $numChild = 0;

    /**
     * @param array $nodeData
     * @param array $keys
     * @throws LocalizedException
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @return int
     * @since 2.0.0
     */
    public function getLeft()
    {
        return $this->left;
    }

    /**
     * @return int
     * @since 2.0.0
     */
    public function getRight()
    {
        return $this->right;
    }

    /**
     * @return string|int
     * @since 2.0.0
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * @return string|int
     * @since 2.0.0
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Return true if node has child
     *
     * @return bool
     * @since 2.0.0
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
