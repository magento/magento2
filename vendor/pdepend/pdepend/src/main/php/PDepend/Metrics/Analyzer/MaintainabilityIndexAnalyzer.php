<?php

/**
 * This file is part of PDepend.
 *
 * PHP Version 5
 *
 * Copyright (c) 2015, Matthias Mullie <pdepend@mullie.eu>.
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
 * @copyright 2015 Matthias Mullie. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace PDepend\Metrics\Analyzer;

use PDepend\Metrics\AbstractCachingAnalyzer;
use PDepend\Metrics\AnalyzerNodeAware;
use PDepend\Source\AST\AbstractASTCallable;
use PDepend\Source\AST\ASTArtifact;
use PDepend\Source\AST\ASTFunction;
use PDepend\Source\AST\ASTInterface;
use PDepend\Source\AST\ASTMethod;

/**
 * This class calculates the Halstead Complexity Measures for the project,
 * methods and functions.
 *
 * @copyright 2015 Matthias Mullie. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class MaintainabilityIndexAnalyzer extends AbstractCachingAnalyzer implements AnalyzerNodeAware
{
    /**
     * Metrics provided by the analyzer implementation.
     */
    const M_MAINTAINABILITY_INDEX = 'mi';

    /**
     * @var AbstractCachingAnalyzer[]
     */
    private $analyzers = array();

    /**
     * Maintainability index is a combination of cyclomatic complexity,
     * halstead volume & lines of code, all of which we already have analyzers
     * for.
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        parent::__construct($options);

        $this->analyzers['ccn'] = new CyclomaticComplexityAnalyzer();
        $this->analyzers['halstead'] = new HalsteadAnalyzer();
        $this->analyzers['loc'] = new NodeLocAnalyzer();
    }

    /**
     * Processes all {@link \PDepend\Source\AST\ASTNamespace} code nodes.
     *
     * @param  \PDepend\Source\AST\ASTNamespace $namespaces
     * @return void
     */
    public function analyze($namespaces)
    {
        // Run CCN, Halstead & LOC analyzers first
        foreach ($this->analyzers as $analyzer) {
            $analyzer->setCache($this->getCache());
            $analyzer->analyze($namespaces);
        }

        if ($this->metrics === null) {
            $this->loadCache();
            $this->fireStartAnalyzer();

            // Init node metrics
            $this->metrics = array();

            foreach ($namespaces as $namespace) {
                $namespace->accept($this);
            }

            $this->fireEndAnalyzer();
            $this->unloadCache();
        }
    }

    /**
     * This method will return an <b>array</b> with all generated metric values
     * for the given <b>$node</b>. If there are no metrics for the requested
     * node, this method will return an empty <b>array</b>.
     *
     * @param \PDepend\Source\AST\ASTArtifact $artifact
     * @return array
     */
    public function getNodeMetrics(ASTArtifact $artifact)
    {
        if (isset($this->metrics[$artifact->getId()])) {
            return $this->metrics[$artifact->getId()];
        }

        return array();
    }

    /**
     * Visits a function node.
     *
     * @param  \PDepend\Source\AST\ASTFunction $function
     * @return void
     */
    public function visitFunction(ASTFunction $function)
    {
        $this->fireStartFunction($function);

        if (false === $this->restoreFromCache($function)) {
            $this->calculateMaintainabilityIndex($function);
        }

        $this->fireEndFunction($function);
    }

    /**
     * Visits a code interface object.
     *
     * @param  \PDepend\Source\AST\ASTInterface $interface
     * @return void
     */
    public function visitInterface(ASTInterface $interface)
    {
        // Empty visit method, we don't want interface metrics
    }

    /**
     * Visits a method node.
     *
     * @param  \PDepend\Source\AST\ASTMethod $method
     * @return void
     */
    public function visitMethod(ASTMethod $method)
    {
        $this->fireStartMethod($method);

        if (false === $this->restoreFromCache($method)) {
            $this->calculateMaintainabilityIndex($method);
        }

        $this->fireEndMethod($method);
    }

    /**
     * @see http://blogs.msdn.com/b/codeanalysis/archive/2007/11/20/maintainability-index-range-and-meaning.aspx
     *
     * @param  \PDepend\Source\AST\AbstractASTCallable $callable
     * @return void
     */
    public function calculateMaintainabilityIndex(AbstractASTCallable $callable)
    {
        $cyclomaticComplexity = $this->analyzers['ccn']->getCcn2($callable);

        $halstead = $this->analyzers['halstead']->getNodeMetrics($callable);
        $halsteadVolume = $halstead[HalsteadAnalyzer::M_HALSTEAD_VOLUME];

        $loc = $this->analyzers['loc']->getNodeMetrics($callable);
        $eloc = $loc[NodeLocAnalyzer::M_EXECUTABLE_LINES_OF_CODE];

        $maintainabilityIndex = 171 - 5.2 * log($halsteadVolume) - 0.23 * $cyclomaticComplexity - 16.2 * log($eloc);
        $maintainabilityIndex = min(100, max(0, $maintainabilityIndex * 100 / 171));
        $this->metrics[$callable->getId()] = array(self::M_MAINTAINABILITY_INDEX => $maintainabilityIndex);
    }
}
