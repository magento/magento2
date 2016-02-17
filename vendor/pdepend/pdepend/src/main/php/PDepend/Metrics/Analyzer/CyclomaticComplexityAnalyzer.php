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

use PDepend\Metrics\AbstractCachingAnalyzer;
use PDepend\Metrics\AnalyzerNodeAware;
use PDepend\Metrics\AnalyzerProjectAware;
use PDepend\Source\AST\AbstractASTCallable;
use PDepend\Source\AST\ASTArtifact;
use PDepend\Source\AST\ASTArtifactList;
use PDepend\Source\AST\ASTFunction;
use PDepend\Source\AST\ASTInterface;
use PDepend\Source\AST\ASTMethod;

/**
 * This class calculates the Cyclomatic Complexity Number(CCN) for the project,
 * methods and functions.
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class CyclomaticComplexityAnalyzer extends AbstractCachingAnalyzer implements AnalyzerNodeAware, AnalyzerProjectAware
{
    /**
     * Metrics provided by the analyzer implementation.
     */
    const M_CYCLOMATIC_COMPLEXITY_1 = 'ccn',
          M_CYCLOMATIC_COMPLEXITY_2 = 'ccn2';

    /**
     * The project Cyclomatic Complexity Number.
     *
     * @var integer
     */
    private $ccn = 0;

    /**
     * Extended Cyclomatic Complexity Number(CCN2) for the project.
     *
     * @var integer
     */
    private $ccn2 = 0;

    /**
     * Processes all {@link \PDepend\Source\AST\ASTNamespace} code nodes.
     *
     * @param  \PDepend\Source\AST\ASTNamespace $namespaces
     * @return void
     */
    public function analyze($namespaces)
    {
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
     * Returns the cyclomatic complexity for the given <b>$node</b> instance.
     *
     * @param  \PDepend\Source\AST\ASTArtifact $node
     * @return integer
     */
    public function getCcn(ASTArtifact $node)
    {
        $metrics = $this->getNodeMetrics($node);
        if (isset($metrics[self::M_CYCLOMATIC_COMPLEXITY_1])) {
            return $metrics[self::M_CYCLOMATIC_COMPLEXITY_1];
        }
        return 0;
    }

    /**
     * Returns the extended cyclomatic complexity for the given <b>$node</b>
     * instance.
     *
     * @param  \PDepend\Source\AST\ASTArtifact $node
     * @return integer
     */
    public function getCcn2(ASTArtifact $node)
    {
        $metrics = $this->getNodeMetrics($node);
        if (isset($metrics[self::M_CYCLOMATIC_COMPLEXITY_2])) {
            return $metrics[self::M_CYCLOMATIC_COMPLEXITY_2];
        }
        return 0;
    }

    /**
     * This method will return an <b>array</b> with all generated metric values
     * for the given <b>$node</b>. If there are no metrics for the requested
     * node, this method will return an empty <b>array</b>.
     *
     * @param  \PDepend\Source\AST\ASTArtifact $artifact
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
     * Provides the project summary metrics as an <b>array</b>.
     *
     * @return array
     */
    public function getProjectMetrics()
    {
        return array(
            self::M_CYCLOMATIC_COMPLEXITY_1  =>  $this->ccn,
            self::M_CYCLOMATIC_COMPLEXITY_2  =>  $this->ccn2
        );
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
            $this->calculateComplexity($function);
        }
        $this->updateProjectMetrics($function->getId());

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
            $this->calculateComplexity($method);
        }
        $this->updateProjectMetrics($method->getId());

        $this->fireEndMethod($method);
    }

    /**
     * Visits methods, functions or closures and calculated their complexity.
     *
     * @param  \PDepend\Source\AST\AbstractASTCallable $callable
     * @return void
     * @since  0.9.8
     */
    public function calculateComplexity(AbstractASTCallable $callable)
    {
        $data = array(
            self::M_CYCLOMATIC_COMPLEXITY_1 => 1,
            self::M_CYCLOMATIC_COMPLEXITY_2 => 1
        );

        foreach ($callable->getChildren() as $child) {
            $data = $child->accept($this, $data);
        }

        $this->metrics[$callable->getId()] = $data;
    }

    /**
     * Stores the complexity of a node and updates the corresponding project
     * values.
     *
     * @param string $nodeId Identifier of the analyzed item.
     *
     * @return void
     * @since  1.0.0
     */
    private function updateProjectMetrics($nodeId)
    {
        $this->ccn  += $this->metrics[$nodeId][self::M_CYCLOMATIC_COMPLEXITY_1];
        $this->ccn2 += $this->metrics[$nodeId][self::M_CYCLOMATIC_COMPLEXITY_2];
    }

    /**
     * Visits a boolean AND-expression.
     *
     * @param \PDepend\Source\AST\ASTNode $node The currently visited node.
     * @param array(string=>integer)      $data The previously calculated ccn values.
     *
     * @return array(string=>integer)
     * @since  0.9.8
     */
    public function visitBooleanAndExpression($node, $data)
    {
        ++$data[self::M_CYCLOMATIC_COMPLEXITY_2];
        return $this->visit($node, $data);
    }

    /**
     * Visits a boolean OR-expression.
     *
     * @param \PDepend\Source\AST\ASTNode $node The currently visited node.
     * @param array(string=>integer)      $data The previously calculated ccn values.
     *
     * @return array(string=>integer)
     * @since  0.9.8
     */
    public function visitBooleanOrExpression($node, $data)
    {
        ++$data[self::M_CYCLOMATIC_COMPLEXITY_2];
        return $this->visit($node, $data);
    }

    /**
     * Visits a switch label.
     *
     * @param \PDepend\Source\AST\ASTNode $node The currently visited node.
     * @param array(string=>integer)      $data The previously calculated ccn values.
     *
     * @return array(string=>integer)
     * @since  0.9.8
     */
    public function visitSwitchLabel($node, $data)
    {
        if (!$node->isDefault()) {
            ++$data[self::M_CYCLOMATIC_COMPLEXITY_1];
            ++$data[self::M_CYCLOMATIC_COMPLEXITY_2];
        }
        return $this->visit($node, $data);
    }

    /**
     * Visits a catch statement.
     *
     * @param \PDepend\Source\AST\ASTNode $node The currently visited node.
     * @param array(string=>integer)      $data The previously calculated ccn values.
     *
     * @return array(string=>integer)
     * @since  0.9.8
     */
    public function visitCatchStatement($node, $data)
    {
        ++$data[self::M_CYCLOMATIC_COMPLEXITY_1];
        ++$data[self::M_CYCLOMATIC_COMPLEXITY_2];

        return $this->visit($node, $data);
    }

    /**
     * Visits an elseif statement.
     *
     * @param \PDepend\Source\AST\ASTNode $node The currently visited node.
     * @param array(string=>integer)      $data The previously calculated ccn values.
     *
     * @return array(string=>integer)
     * @since  0.9.8
     */
    public function visitElseIfStatement($node, $data)
    {
        ++$data[self::M_CYCLOMATIC_COMPLEXITY_1];
        ++$data[self::M_CYCLOMATIC_COMPLEXITY_2];

        return $this->visit($node, $data);
    }

    /**
     * Visits a for statement.
     *
     * @param \PDepend\Source\AST\ASTNode $node The currently visited node.
     * @param array(string=>integer)      $data The previously calculated ccn values.
     *
     * @return array(string=>integer)
     * @since  0.9.8
     */
    public function visitForStatement($node, $data)
    {
        ++$data[self::M_CYCLOMATIC_COMPLEXITY_1];
        ++$data[self::M_CYCLOMATIC_COMPLEXITY_2];

        return $this->visit($node, $data);
    }

    /**
     * Visits a foreach statement.
     *
     * @param \PDepend\Source\AST\ASTNode $node The currently visited node.
     * @param array(string=>integer)      $data The previously calculated ccn values.
     *
     * @return array(string=>integer)
     * @since  0.9.8
     */
    public function visitForeachStatement($node, $data)
    {
        ++$data[self::M_CYCLOMATIC_COMPLEXITY_1];
        ++$data[self::M_CYCLOMATIC_COMPLEXITY_2];

        return $this->visit($node, $data);
    }

    /**
     * Visits an if statement.
     *
     * @param \PDepend\Source\AST\ASTNode $node The currently visited node.
     * @param array(string=>integer)      $data The previously calculated ccn values.
     *
     * @return array(string=>integer)
     * @since  0.9.8
     */
    public function visitIfStatement($node, $data)
    {
        ++$data[self::M_CYCLOMATIC_COMPLEXITY_1];
        ++$data[self::M_CYCLOMATIC_COMPLEXITY_2];

        return $this->visit($node, $data);
    }

    /**
     * Visits a logical AND expression.
     *
     * @param \PDepend\Source\AST\ASTNode $node The currently visited node.
     * @param array(string=>integer)      $data The previously calculated ccn values.
     *
     * @return array(string=>integer)
     * @since  0.9.8
     */
    public function visitLogicalAndExpression($node, $data)
    {
        ++$data[self::M_CYCLOMATIC_COMPLEXITY_2];
        return $this->visit($node, $data);
    }

    /**
     * Visits a logical OR expression.
     *
     * @param \PDepend\Source\AST\ASTNode $node The currently visited node.
     * @param array(string=>integer)      $data The previously calculated ccn values.
     *
     * @return array(string=>integer)
     * @since  0.9.8
     */
    public function visitLogicalOrExpression($node, $data)
    {
        ++$data[self::M_CYCLOMATIC_COMPLEXITY_2];
        return $this->visit($node, $data);
    }

    /**
     * Visits a ternary operator.
     *
     * @param \PDepend\Source\AST\ASTNode $node The currently visited node.
     * @param array(string=>integer)      $data The previously calculated ccn values.
     *
     * @return array(string=>integer)
     * @since  0.9.8
     */
    public function visitConditionalExpression($node, $data)
    {
        ++$data[self::M_CYCLOMATIC_COMPLEXITY_1];
        ++$data[self::M_CYCLOMATIC_COMPLEXITY_2];

        return $this->visit($node, $data);
    }

    /**
     * Visits a while-statement.
     *
     * @param \PDepend\Source\AST\ASTNode $node The currently visited node.
     * @param array(string=>integer)      $data The previously calculated ccn values.
     *
     * @return array(string=>integer)
     * @since  0.9.8
     */
    public function visitWhileStatement($node, $data)
    {
        ++$data[self::M_CYCLOMATIC_COMPLEXITY_1];
        ++$data[self::M_CYCLOMATIC_COMPLEXITY_2];

        return $this->visit($node, $data);
    }

    /**
     * Visits a do/while-statement.
     *
     * @param \PDepend\Source\AST\ASTNode $node The currently visited node.
     * @param array(string=>integer)      $data The previously calculated ccn values.
     *
     * @return array(string=>integer)
     * @since  0.9.12
     */
    public function visitDoWhileStatement($node, $data)
    {
        ++$data[self::M_CYCLOMATIC_COMPLEXITY_1];
        ++$data[self::M_CYCLOMATIC_COMPLEXITY_2];

        return $this->visit($node, $data);
    }
}
