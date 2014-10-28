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
 