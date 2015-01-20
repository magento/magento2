<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data;

/**
 * Tests for \Magento\Framework\Data\FormFactory
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class TreeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Data\Tree
     */
    protected $_tree;

    public function setUp()
    {
        $this->_tree = new Tree();
    }

    public function testTreeOperations()
    {
        $newNode1 = new Tree\Node('abc', 'node1', $this->_tree);
        $this->_tree->addNode($newNode1);
        $newNode2 = new Tree\Node('def', 'node2', $this->_tree);
        $this->_tree->addNode($newNode2, $newNode1);
        $newNode3 = new Tree\Node('ghi', 'node3', $this->_tree);
        $this->_tree->addNode($newNode3, $newNode1);
        $data1 = ['j', 'k', 'l'];
        $this->_tree->appendChild($data1, $newNode3);
        $newNode4 = new Tree\Node('mno', 'node4', $this->_tree);
        $this->_tree->appendChild($newNode4, $newNode3);

        $this->_tree->removeNode($newNode4);
        $this->_tree->removeNode($newNode3);
        $this->_tree->removeNode($newNode2);
        $this->_tree->removeNode($newNode1);

        $this->assertEmpty($this->_tree->getNodes()->getNodes());
    }
}
