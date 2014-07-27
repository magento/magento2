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

namespace Magento\Framework\Data\Tree\Node;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Data\Tree\Node\Collection
     */
    protected $collection;

    public function setUp()
    {
        $tree = new \Magento\Framework\Data\Tree();
        $node = new \Magento\Framework\Data\Tree\Node(['id' => 'root'], 'id', $tree);
        $this->collection = new Collection($node);
    }

    public function testAdd()
    {
        $tree = new \Magento\Framework\Data\Tree();
        $this->assertSame($this->collection->count(), 0);
        $node = new \Magento\Framework\Data\Tree\Node(['id' => 'node1'], 'id', $tree);
        $this->collection->add($node);
        $this->assertSame($this->collection->count(), 1);
    }

    public function testOffsets()
    {
        $tree = new \Magento\Framework\Data\Tree();
        $this->assertSame($this->collection->count(), 0);
        $node = new \Magento\Framework\Data\Tree\Node(['id' => 'node1'], 'id', $tree);
        $this->collection->add($node);
        $this->assertSame($this->collection->offsetExists('node1'), true);
        $this->collection->offsetSet('node1', 'Hello');
        $this->assertSame($this->collection->offsetExists('node1'), true);
        $this->assertSame($this->collection->offsetGet('node1'), 'Hello');
        $this->collection->offsetUnset('node1');
        $this->assertSame($this->collection->offsetExists('node1'), false);
    }

    public function testDelete()
    {
        $tree = new \Magento\Framework\Data\Tree();
        $this->assertSame($this->collection->count(), 0);
        $node = new \Magento\Framework\Data\Tree\Node(['id' => 'node1'], 'id', $tree);
        $this->collection->add($node);
        $this->assertSame($this->collection->count(), 1);
        $this->collection->delete($node);
        $this->assertSame($this->collection->count(), 0);
    }

    public function testLastNode()
    {
        $tree = new \Magento\Framework\Data\Tree();
        $node1 = new \Magento\Framework\Data\Tree\Node(['id' => 'node1'], 'id', $tree);
        $this->collection->add($node1);
        $node2 = new \Magento\Framework\Data\Tree\Node(['id' => 'node2'], 'id', $tree);
        $this->collection->add($node2);
        $this->assertSame($this->collection->lastNode(), $node2);
        $node3 = new \Magento\Framework\Data\Tree\Node(['id' => 'node3'], 'id', $tree);
        $this->collection->add($node3);

        $this->assertSame($this->collection->lastNode(), $node3);
        $this->assertSame($this->collection->lastNode(), $node3);
        $this->collection->delete($node3);
        $this->assertSame($this->collection->lastNode(), $node2);
        $this->assertSame($this->collection->lastNode(), $node2);
    }

    public function testSearchById()
    {
        $tree = new \Magento\Framework\Data\Tree();
        $node1 = new \Magento\Framework\Data\Tree\Node(['id' => 'node1'], 'id', $tree);
        $this->collection->add($node1);
        $node2 = new \Magento\Framework\Data\Tree\Node(['id' => 'node2'], 'id', $tree);
        $this->collection->add($node2);
        $this->assertSame($this->collection->lastNode(), $node2);
        $node3 = new \Magento\Framework\Data\Tree\Node(['id' => 'node3'], 'id', $tree);
        $this->collection->add($node3);

        $this->assertSame($this->collection->searchById('node2'), $node2);
    }
}
 