<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Test\Unit;

class GraphTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param array $nodes
     * @param array $relations
     * @dataProvider constructorErrorDataProvider
     */
    public function testConstructorError($nodes, $relations)
    {
        $this->expectException(\InvalidArgumentException::class);

        new \Magento\Framework\Data\Graph($nodes, $relations);
    }

    /**
     * @return array
     */
    public function constructorErrorDataProvider()
    {
        return [
            'duplicate nodes' => [[1, 2, 2], []],
            'self-link' => [[1, 2], [[1, 2], [2, 2]]],
            'broken reference "from"' => [[1, 2], [[1, 2], [3, 1]]],
            'broken reference "to"' => [[1, 2], [[1, 2], [1, 3]]]
        ];
    }

    /**
     * \Exceptions are covered by testConstructorError()
     */
    public function testAddRelation()
    {
        $model = new \Magento\Framework\Data\Graph([1, 2, 3], [[1, 2], [2, 3]]);
        $this->assertEquals([1 => [2 => 2], 2 => [3 => 3]], $model->getRelations());
        $this->assertSame($model, $model->addRelation(3, 1));
        $this->assertEquals([1 => [2 => 2], 2 => [3 => 3], 3 => [1 => 1]], $model->getRelations());
    }

    public function testGetRelations()
    {
        // directional case is covered by testAddRelation()

        // inverse
        $model = new \Magento\Framework\Data\Graph([1, 2, 3], [[1, 2], [2, 3]]);
        $this->assertEquals(
            [2 => [1 => 1], 3 => [2 => 2]],
            $model->getRelations(\Magento\Framework\Data\Graph::INVERSE)
        );

        // non-directional
        $this->assertEquals(
            [1 => [2 => 2], 2 => [1 => 1, 3 => 3], 3 => [2 => 2]],
            $model->getRelations(\Magento\Framework\Data\Graph::NON_DIRECTIONAL)
        );
    }

    public function testFindCycle()
    {
        $nodes = [1, 2, 3, 4];
        $model = new \Magento\Framework\Data\Graph($nodes, [[1, 2], [2, 3], [3, 4]]);
        $this->assertEquals([], $model->findCycle());

        $model = new \Magento\Framework\Data\Graph($nodes, [[1, 2], [2, 3], [3, 4], [4, 2]]);
        $this->assertEquals([], $model->findCycle(1));
        $cycle = $model->findCycle();
        sort($cycle);
        $this->assertEquals([2, 2, 3, 4], $cycle);
        $this->assertEquals([3, 4, 2, 3], $model->findCycle(3));

        $model = new \Magento\Framework\Data\Graph(
            $nodes,
            [[1, 2], [2, 3], [3, 4], [4, 2], [3, 1]]
        );
        //find cycles for each node
        $cycles = $model->findCycle(null, false);
        $this->assertEquals(
            [[1, 2, 3, 1], [2, 3, 4, 2], [3, 4, 2, 3], [4, 2, 3, 4]],
            $cycles
        );
    }

    public function testDfs()
    {
        $model = new \Magento\Framework\Data\Graph([1, 2, 3, 4, 5], [[1, 2], [2, 3], [4, 5]]);

        // directional
        $this->assertEquals([1, 2, 3], $model->dfs(1, 3));
        $this->assertEquals([], $model->dfs(3, 1));
        $this->assertEquals([4, 5], $model->dfs(4, 5));
        $this->assertEquals([], $model->dfs(1, 5));

        // inverse
        $this->assertEquals([3, 2, 1], $model->dfs(3, 1, \Magento\Framework\Data\Graph::INVERSE));

        // non-directional
        $model = new \Magento\Framework\Data\Graph([1, 2, 3], [[2, 1], [2, 3]]);
        $this->assertEquals([], $model->dfs(1, 3, \Magento\Framework\Data\Graph::DIRECTIONAL));
        $this->assertEquals([], $model->dfs(3, 1, \Magento\Framework\Data\Graph::INVERSE));
        $this->assertEquals([1, 2, 3], $model->dfs(1, 3, \Magento\Framework\Data\Graph::NON_DIRECTIONAL));
    }

    public function testFindPathsToReachableNodes()
    {
        $model = new \Magento\Framework\Data\Graph([1, 2, 3, 4, 5], [[1, 2], [1, 3], [1, 4], [4, 5]]);

        // directional
        $paths = $model->findPathsToReachableNodes(1);
        ksort($paths);
        $this->assertEquals([1 => [1], 2 => [1, 2], 3 => [1, 3], 4 => [1, 4], 5 => [1, 4, 5]], $paths);

        // inverse
        $paths = $model->findPathsToReachableNodes(5, \Magento\Framework\Data\Graph::INVERSE);
        ksort($paths);
        $this->assertEquals([1 => [5, 4, 1], 4 => [5, 4], 5 => [5]], $paths);

        // non-directional
        $paths = $model->findPathsToReachableNodes(5, \Magento\Framework\Data\Graph::NON_DIRECTIONAL);
        ksort($paths);
        $this->assertEquals([1 => [5, 4, 1], 2 => [5, 4, 1, 2], 3 => [5, 4, 1, 3], 4 => [5, 4], 5 => [5]], $paths);
    }
}
