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
     * Traverse the graph using breadth-search traversal from a root
     *
     * @return void
     */
    public function traverseGraph($fromNode, $mode = self::DIRECTIONAL)
    {
        $this->_assertNode($fromNode, true);
        $this->_traverseGraphBFS($fromNode, $this->getRelations($mode));
    }

    /**
     * Traverse helper method
     *
     * @param $fromNode
     * @param $graph
     * @return void
     */
    protected function _traverseGraphBFS($fromNode, $graph)
    {
        $queue = [$fromNode];
        $visited = [];
        $this->paths[$fromNode] = [$fromNode];
        while (!empty($queue)) {
            $node = array_shift($queue);
            if (!empty($graph[$node])) {
                foreach ($graph[$node] as $child) {
                    if (!isset($visited[$child])) {
                        $this->paths[$child] = array_merge($this->paths[$node], [$child]);
                        $visited[$child] = $child;
                        $queue[] = $child;
                    }
                }
            }
        }
    }

    /**
     * Get shortest chain to node
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
