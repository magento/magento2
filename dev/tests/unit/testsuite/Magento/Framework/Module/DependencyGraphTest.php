<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

class DependencyGraphTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getChainDataProvider
     * @param array $nodes
     * @param array $relations
     * @param string $from
     * @param string $to
     * @param int $mode
     * @param array $chain
     */
    public function testGetChain($nodes, $relations, $from, $to, $mode, $chain)
    {
        $dependencyGraph = new DependencyGraph($nodes, $relations);
        $dependencyGraph->traverseGraph($from, $mode);
        $this->assertEquals($chain, $dependencyGraph->getChain($to));
    }

    /**
     * @return array
     */
    public function getChainDataProvider()
    {
        return [
            [
                ['A', 'B', 'C', 'D', 'E'],
                [['A', 'B'], ['A', 'C'], ['B', 'D'], ['B', 'E'], ['D', 'E']],
                'A',
                'E',
                DependencyGraph::DIRECTIONAL ,
                ['A', 'B', 'E']
            ],
            [
                ['A', 'B', 'C', 'D', 'E'],
                [['A', 'B'], ['A', 'C'], ['B', 'D'], ['B', 'E'], ['D', 'E']],
                'E',
                'A',
                DependencyGraph::INVERSE ,
                ['E', 'B', 'A']
            ],
            [
                ['A', 'B', 'C', 'D', 'E'],
                [['A', 'C'], ['C', 'B'], ['C', 'E'], ['B', 'D'], ['D', 'E']],
                'A',
                'E',
                DependencyGraph::DIRECTIONAL,
                ['A', 'C', 'E']
            ],
            [
                ['A', 'B', 'C', 'D', 'E'],
                [['A', 'C'], ['C', 'B'], ['C', 'E'], ['B', 'D'], ['D', 'E']],
                'E',
                'A',
                DependencyGraph::INVERSE,
                ['E', 'C', 'A']
            ],
        ];
    }
}
