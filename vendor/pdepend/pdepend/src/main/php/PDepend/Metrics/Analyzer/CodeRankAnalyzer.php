<?php
/**
 * This file is part of PDepend.
 *
 * PHP Version 5
 *
 * Copyright (c) 2008-2015, Manuel Pichler <mapi@pdepend.org>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Manuel Pichler nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace PDepend\Metrics\Analyzer;

use PDepend\Metrics\AbstractAnalyzer;
use PDepend\Metrics\Analyzer\CodeRankAnalyzer\StrategyFactory;
use PDepend\Metrics\AnalyzerNodeAware;
use PDepend\Source\AST\ASTArtifact;
use PDepend\Source\AST\ASTArtifactList;

/**
 * Calculates the code rank metric for classes and namespaces.
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class CodeRankAnalyzer extends AbstractAnalyzer implements AnalyzerNodeAware
{
    /**
     * Metrics provided by the analyzer implementation.
     */
    const M_CODE_RANK         = 'cr',
          M_REVERSE_CODE_RANK = 'rcr';

    /**
     * The used damping factor.
     */
    const DAMPING_FACTOR = 0.85;

    /**
     * Number of loops for the code range calculation.
     */
    const ALGORITHM_LOOPS = 25;

    /**
     * Option key for the code rank mode.
     */
    const STRATEGY_OPTION = 'coderank-mode';

    /**
     * All found nodes.
     *
     * @var array(string=>array)
     */
    private $nodes = array();

    /**
     * List of node collect strategies.
     *
     * @var \PDepend\Metrics\Analyzer\CodeRankAnalyzer\CodeRankStrategyI[]
     */
    private $strategies = array();

    /**
     * Hash with all calculated node metrics.
     *
     * <code>
     * array(
     *     '0375e305-885a-4e91-8b5c-e25bda005438'  =>  array(
     *         'loc'    =>  42,
     *         'ncloc'  =>  17,
     *         'cc'     =>  12
     *     ),
     *     'e60c22f0-1a63-4c40-893e-ed3b35b84d0b'  =>  array(
     *         'loc'    =>  42,
     *         'ncloc'  =>  17,
     *         'cc'     =>  12
     *     )
     * )
     * </code>
     *
     * @var array(string=>array)
     */
    private $nodeMetrics = null;

    /**
     * Processes all {@link \PDepend\Source\AST\ASTNamespace} code nodes.
     *
     * @param \PDepend\Source\AST\ASTNamespace[] $namespaces
     * @return void
     */
    public function analyze($namespaces)
    {
        if ($this->nodeMetrics === null) {
            $this->fireStartAnalyzer();

            $factory = new StrategyFactory();
            if (isset($this->options[self::STRATEGY_OPTION])) {
                foreach ($this->options[self::STRATEGY_OPTION] as $identifier) {
                    $this->strategies[] = $factory->createStrategy($identifier);
                }
            } else {
                $this->strategies[] = $factory->createDefaultStrategy();
            }

            // Register all listeners
            foreach ($this->getVisitListeners() as $listener) {
                foreach ($this->strategies as $strategy) {
                    $strategy->addVisitListener($listener);
                }
            }

            foreach ($namespaces as $namespace) {
                // Traverse all strategies
                foreach ($this->strategies as $strategy) {
                    $namespace->accept($strategy);
                }
            }

            // Collect all nodes
            foreach ($this->strategies as $strategy) {
                $collected    = $strategy->getCollectedNodes();
                $this->nodes = array_merge_recursive($collected, $this->nodes);
            }

            // Init node metrics
            $this->nodeMetrics = array();

            // Calculate code rank metrics
            $this->buildCodeRankMetrics();

            $this->fireEndAnalyzer();
        }
    }

    /**
     * This method will return an <b>array</b> with all generated metric values
     * for the given <b>$node</b>. If there are no metrics for the requested
     * node, this method will return an empty <b>array</b>.
     *
     * @param  \PDepend\Source\AST\ASTArtifact $artifact
     * @return array(string=>mixed)
     */
    public function getNodeMetrics(ASTArtifact $artifact)
    {
        if (isset($this->nodeMetrics[$artifact->getId()])) {
            return $this->nodeMetrics[$artifact->getId()];
        }
        return array();
    }

    /**
     * Generates the forward and reverse code rank for the given <b>$nodes</b>.
     *
     * @return void
     */
    protected function buildCodeRankMetrics()
    {
        foreach (array_keys($this->nodes) as $id) {
            $this->nodeMetrics[$id] = array(
                self::M_CODE_RANK          =>  0,
                self::M_REVERSE_CODE_RANK  =>  0
            );
        }
        foreach ($this->computeCodeRank('out', 'in') as $id => $rank) {
            $this->nodeMetrics[$id][self::M_CODE_RANK] = $rank;
        }
        foreach ($this->computeCodeRank('in', 'out') as $id => $rank) {
            $this->nodeMetrics[$id][self::M_REVERSE_CODE_RANK] = $rank;
        }
    }

    /**
     * Calculates the code rank for the given <b>$nodes</b> set.
     *
     * @param string $id1 Identifier for the incoming edges.
     * @param string $id2 Identifier for the outgoing edges.
     *
     * @return array(string=>float)
     */
    protected function computeCodeRank($id1, $id2)
    {
        $dampingFactory = self::DAMPING_FACTOR;

        $ranks = array();

        foreach (array_keys($this->nodes) as $name) {
            $ranks[$name] = 1;
        }

        for ($i = 0; $i < self::ALGORITHM_LOOPS; $i++) {
            foreach ($this->nodes as $name => $info) {
                $rank = 0;
                foreach ($info[$id1] as $ref) {
                    $previousRank = $ranks[$ref];
                    $refCount     = count($this->nodes[$ref][$id2]);

                    $rank += ($previousRank / $refCount);
                }
                $ranks[$name] = ((1 - $dampingFactory)) + $dampingFactory * $rank;
            }
        }
        return $ranks;
    }
}
