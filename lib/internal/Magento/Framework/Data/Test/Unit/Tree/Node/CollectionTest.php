<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Data\Test\Unit\Tree\Node;

use \Magento\Framework\Data\Tree\Node\Collection;

class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Data\Tree\Node\Collection
     */
    protected $collection;

    protected function setUp(): void
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
