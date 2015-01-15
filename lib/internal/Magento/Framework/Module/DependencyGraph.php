<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

class DependencyGraph extends \Magento\Framework\Data\Graph
{
    /**
     * @var array
     */
    private $paths;

    /**
     * Traverse the graph using depth-search traversal
     *
     * @return void
     */
    public function traverseGraph($fromNode, $mode = self::DIRECTIONAL)
    {
        $this->_assertNode($fromNode, true);
        //$this->_traverseGraph($fromNode, $this->getRelations($mode), [$fromNode]);
        $this->_traverseGraphBFS($fromNode, $this->getRelations($mode));
    }

    protected function _traverseGraph($fromNode, $graph, $path = [], &$visited = [])
    {
        $visited[$fromNode] = $fromNode;
        if (isset($graph[$fromNode])) {
            foreach ($graph[$fromNode] as $node) {
                if (!isset($visited[$node])) {
                    $path[] = $node;
                    $this->paths[$node] = $path;
                    $this->_traverseGraph($node, $graph, $path, $visited);
                }
            }
        }
    }

    protected function _traverseGraphBFS($fromNode, $graph)
    {
        $queue = [$fromNode];
        $visited = [];
        $this->paths[$fromNode] = [$fromNode];
        while (!empty($queue)) {
            $node = array_shift($queue);
            $visited[$node] = $node;
            if (isset($graph[$node])) {
                foreach ($graph[$node] as $child) {
                    if (!isset($visited[$child])) {
                        $this->paths[$child] = array_merge($this->paths[$node], [$child]);
                        $queue[] = $child;
                    }
                }
            }
        }
    }

    /**
     * Get chain to node
     * Note that this is not necessarily the only chain
     *
     * @param string $toNode
     * @return array
     */
    public function getChain($toNode)
    {
        return isset($this->paths[$toNode]) ? $this->paths[$toNode] : [];
    }
}
