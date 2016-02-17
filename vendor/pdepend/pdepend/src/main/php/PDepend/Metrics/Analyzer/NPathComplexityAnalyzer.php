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
use PDepend\Metrics\AnalyzerFilterAware;
use PDepend\Metrics\AnalyzerNodeAware;
use PDepend\Source\AST\AbstractASTCallable;
use PDepend\Source\AST\ASTArtifact;
use PDepend\Source\AST\ASTBooleanAndExpression;
use PDepend\Source\AST\ASTBooleanOrExpression;
use PDepend\Source\AST\ASTConditionalExpression;
use PDepend\Source\AST\ASTExpression;
use PDepend\Source\AST\ASTFunction;
use PDepend\Source\AST\ASTInterface;
use PDepend\Source\AST\ASTLogicalAndExpression;
use PDepend\Source\AST\ASTLogicalOrExpression;
use PDepend\Source\AST\ASTLogicalXorExpression;
use PDepend\Source\AST\ASTMethod;
use PDepend\Source\AST\ASTStatement;
use PDepend\Source\AST\ASTSwitchLabel;
use PDepend\Util\MathUtil;

/**
 * This analyzer calculates the NPath complexity of functions and methods. The
 * NPath complexity metric measures the acyclic execution paths through a method
 * or function. See Nejmeh, Communications of the ACM Feb 1988 pp 188-200.
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class NPathComplexityAnalyzer extends AbstractCachingAnalyzer implements AnalyzerFilterAware, AnalyzerNodeAware
{
    /**
     * Metrics provided by the analyzer implementation.
     */
    const M_NPATH_COMPLEXITY = 'npath';

    /**
     * Processes all {@link \PDepend\Source\AST\ASTNamespace} code nodes.
     *
     * @param  \PDepend\Source\AST\ASTNamespace[] $namespaces
     * @return void
     */
    public function analyze($namespaces)
    {
        if ($this->metrics === null) {
            $this->loadCache();
            $this->fireStartAnalyzer();

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
     * for the node with the given <b>$id</b> identifier. If there are no
     * metrics for the requested node, this method will return an empty <b>array</b>.
     *
     * <code>
     * array(
     *     'npath'  =>  '17'
     * )
     * </code>
     *
     * @param  \PDepend\Source\AST\ASTArtifact $artifact
     * @return array
     */
    public function getNodeMetrics(ASTArtifact $artifact)
    {
        $metric = array();
        if (isset($this->metrics[$artifact->getId()])) {
            $metric = array(self::M_NPATH_COMPLEXITY  =>  $this->metrics[$artifact->getId()]);
        }
        return $metric;
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

        $this->fireEndFunction($function);
    }

    /**
     * Visits a method node.
     *
     * @param \PDepend\Source\AST\ASTMethod $method
     *
     * @return void
     */
    public function visitMethod(ASTMethod $method)
    {
        $this->fireStartMethod($method);

        if (false === $this->restoreFromCache($method)) {
            $this->calculateComplexity($method);
        }

        $this->fireEndMethod($method);
    }

    /**
     * This method will calculate the NPath complexity for the given callable
     * instance.
     *
     * @param  \PDepend\Source\AST\AbstractASTCallable $callable
     * @return void
     * @since  0.9.12
     */
    protected function calculateComplexity(AbstractASTCallable $callable)
    {
        $npath = '1';
        foreach ($callable->getChildren() as $child) {
            $stmt  = $child->accept($this, $npath);
            $npath = MathUtil::mul($npath, $stmt);
        }

        $this->metrics[$callable->getId()] = $npath;
    }

    /**
     * This method calculates the NPath Complexity of a conditional-statement,
     * the meassured value is then returned as a string.
     *
     * <code>
     * <expr1> ? <expr2> : <expr3>
     *
     * -- NP(?) = NP(<expr1>) + NP(<expr2>) + NP(<expr3>) + 2 --
     * </code>
     *
     * @param  \PDepend\Source\AST\ASTNode
     * @param  string                      $data
     * @return string
     * @since  0.9.12
     */
    public function visitConditionalExpression($node, $data)
    {
        // New PHP 5.3 ifsetor-operator $x ?: $y
        if (count($node->getChildren()) === 1) {
            $npath = '4';
        } else {
            $npath = '3';
        }

        foreach ($node->getChildren() as $child) {
            if (($cn = $this->sumComplexity($child)) === '0') {
                $cn = '1';
            }
            $npath = MathUtil::add($npath, $cn);
        }

        return MathUtil::mul($npath, $data);
    }

    /**
     * This method calculates the NPath Complexity of a do-while-statement, the
     * meassured value is then returned as a string.
     *
     * <code>
     * do
     *   <do-range>
     * while (<expr>)
     * S;
     *
     * -- NP(do) = NP(<do-range>) + NP(<expr>) + 1 --
     * </code>
     *
     * @param \PDepend\Source\AST\ASTNode $node The currently visited node.
     * @param string                      $data The previously calculated npath value.
     *
     * @return string
     * @since  0.9.12
     */
    public function visitDoWhileStatement($node, $data)
    {
        $stmt = $node->getChild(0)->accept($this, 1);
        $expr = $this->sumComplexity($node->getChild(1));

        $npath = MathUtil::add($expr, $stmt);
        $npath = MathUtil::add($npath, '1');

        return MathUtil::mul($npath, $data);
    }

    /**
     * This method calculates the NPath Complexity of an elseif-statement, the
     * meassured value is then returned as a string.
     *
     * <code>
     * elseif (<expr>)
     *   <elseif-range>
     * S;
     *
     * -- NP(elseif) = NP(<elseif-range>) + NP(<expr>) + 1 --
     *
     *
     * elseif (<expr>)
     *   <elseif-range>
     * else
     *   <else-range>
     * S;
     *
     * -- NP(if) = NP(<if-range>) + NP(<expr>) + NP(<else-range> --
     * </code>
     *
     * @param \PDepend\Source\AST\ASTNode $node The currently visited node.
     * @param string                      $data The previously calculated npath value.
     *
     * @return string
     * @since  0.9.12
     */
    public function visitElseIfStatement($node, $data)
    {
        $npath = $this->sumComplexity($node->getChild(0));
        foreach ($node->getChildren() as $child) {
            if ($child instanceof ASTStatement) {
                $expr  = $child->accept($this, 1);
                $npath = MathUtil::add($npath, $expr);
            }
        }

        if (!$node->hasElse()) {
            $npath = MathUtil::add($npath, '1');
        }

        return MathUtil::mul($npath, $data);
    }

    /**
     * This method calculates the NPath Complexity of a for-statement, the
     * meassured value is then returned as a string.
     *
     * <code>
     * for (<expr1>; <expr2>; <expr3>)
     *   <for-range>
     * S;
     *
     * -- NP(for) = NP(<for-range>) + NP(<expr1>) + NP(<expr2>) + NP(<expr3>) + 1 --
     * </code>
     *
     * @param \PDepend\Source\AST\ASTNode $node The currently visited node.
     * @param string                      $data The previously calculated npath value.
     *
     * @return string
     * @since  0.9.12
     */
    public function visitForStatement($node, $data)
    {
        $npath = '1';
        foreach ($node->getChildren() as $child) {
            if ($child instanceof ASTStatement) {
                $stmt  = $child->accept($this, 1);
                $npath = MathUtil::add($npath, $stmt);
            } elseif ($child instanceof ASTExpression) {
                $expr  = $this->sumComplexity($child);
                $npath = MathUtil::add($npath, $expr);
            }
        }

        return MathUtil::mul($npath, $data);
    }

    /**
     * This method calculates the NPath Complexity of a for-statement, the
     * meassured value is then returned as a string.
     *
     * <code>
     * fpreach (<expr>)
     *   <foreach-range>
     * S;
     *
     * -- NP(foreach) = NP(<foreach-range>) + NP(<expr>) + 1 --
     * </code>
     *
     * @param \PDepend\Source\AST\ASTNode $node The currently visited node.
     * @param string                      $data The previously calculated npath value.
     *
     * @return string
     * @since  0.9.12
     */
    public function visitForeachStatement($node, $data)
    {
        $npath = $this->sumComplexity($node->getChild(0));
        $npath = MathUtil::add($npath, '1');

        foreach ($node->getChildren() as $child) {
            if ($child instanceof ASTStatement) {
                $stmt  = $child->accept($this, 1);
                $npath = MathUtil::add($npath, $stmt);
            }
        }

        return MathUtil::mul($npath, $data);
    }

    /**
     * This method calculates the NPath Complexity of an if-statement, the
     * meassured value is then returned as a string.
     *
     * <code>
     * if (<expr>)
     *   <if-range>
     * S;
     *
     * -- NP(if) = NP(<if-range>) + NP(<expr>) + 1 --
     *
     *
     * if (<expr>)
     *   <if-range>
     * else
     *   <else-range>
     * S;
     *
     * -- NP(if) = NP(<if-range>) + NP(<expr>) + NP(<else-range> --
     * </code>
     *
     * @param \PDepend\Source\AST\ASTNode $node The currently visited node.
     * @param string                      $data The previously calculated npath value.
     *
     * @return string
     * @since  0.9.12
     */
    public function visitIfStatement($node, $data)
    {
        $npath = $this->sumComplexity($node->getChild(0));

        foreach ($node->getChildren() as $child) {
            if ($child instanceof ASTStatement) {
                $stmt  = $child->accept($this, 1);
                $npath = MathUtil::add($npath, $stmt);
            }
        }

        if (!$node->hasElse()) {
            $npath = MathUtil::add($npath, '1');
        }

        return MathUtil::mul($npath, $data);
    }

    /**
     * This method calculates the NPath Complexity of a return-statement, the
     * meassured value is then returned as a string.
     *
     * <code>
     * return <expr>;
     *
     * -- NP(return) = NP(<expr>) --
     * </code>
     *
     * @param \PDepend\Source\AST\ASTNode $node The currently visited node.
     * @param string                      $data The previously calculated npath value.
     *
     * @return string
     * @since  0.9.12
     */
    public function visitReturnStatement($node, $data)
    {
        if (($npath = $this->sumComplexity($node)) === '0') {
            return $data;
        }
        return MathUtil::mul($npath, $data);
    }

    /**
     * This method calculates the NPath Complexity of a switch-statement, the
     * meassured value is then returned as a string.
     *
     * <code>
     * switch (<expr>)
     *   <case-range1>
     *   <case-range2>
     *   ...
     *   <default-range>
     *
     * -- NP(switch) = NP(<expr>) + NP(<default-range>) +  NP(<case-range1>) ... --
     * </code>
     *
     * @param \PDepend\Source\AST\ASTNode $node The currently visited node.
     * @param string                      $data The previously calculated npath value.
     *
     * @return string
     * @since  0.9.12
     */
    public function visitSwitchStatement($node, $data)
    {
        $npath = $this->sumComplexity($node->getChild(0));
        foreach ($node->getChildren() as $child) {
            if ($child instanceof ASTSwitchLabel) {
                $label = $child->accept($this, 1);
                $npath = MathUtil::add($npath, $label);
            }
        }
        return MathUtil::mul($npath, $data);
    }

    /**
     * This method calculates the NPath Complexity of a try-catch-statement, the
     * meassured value is then returned as a string.
     *
     * <code>
     * try
     *   <try-range>
     * catch
     *   <catch-range>
     *
     * -- NP(try) = NP(<try-range>) + NP(<catch-range>) --
     *
     *
     * try
     *   <try-range>
     * catch
     *   <catch-range1>
     * catch
     *   <catch-range2>
     * ...
     *
     * -- NP(try) = NP(<try-range>) + NP(<catch-range1>) + NP(<catch-range2>) ... --
     * </code>
     *
     * @param \PDepend\Source\AST\ASTNode $node The currently visited node.
     * @param string                      $data The previously calculated npath value.
     *
     * @return string
     * @since  0.9.12
     */
    public function visitTryStatement($node, $data)
    {
        $npath = '0';
        foreach ($node->getChildren() as $child) {
            if ($child instanceof ASTStatement) {
                $stmt  = $child->accept($this, 1);
                $npath = MathUtil::add($npath, $stmt);
            }
        }
        return MathUtil::mul($npath, $data);
    }

    /**
     * This method calculates the NPath Complexity of a while-statement, the
     * meassured value is then returned as a string.
     *
     * <code>
     * while (<expr>)
     *   <while-range>
     * S;
     *
     * -- NP(while) = NP(<while-range>) + NP(<expr>) + 1 --
     * </code>
     *
     * @param \PDepend\Source\AST\ASTNode $node The currently visited node.
     * @param string                      $data The previously calculated npath value.
     *
     * @return string
     * @since  0.9.12
     */
    public function visitWhileStatement($node, $data)
    {
        $expr = $this->sumComplexity($node->getChild(0));
        $stmt = $node->getChild(1)->accept($this, 1);

        $npath = MathUtil::add($expr, $stmt);
        $npath = MathUtil::add($npath, '1');

        return MathUtil::mul($npath, $data);
    }

    /**
     * Calculates the expression sum of the given node.
     *
     * @param \PDepend\Source\AST\ASTNode $node The currently visited node.
     *
     * @return string
     * @since  0.9.12
     * @todo   I don't like this method implementation, it should be possible to
     *       implement this method with more visitor behavior for the boolean
     *       and logical expressions.
     */
    public function sumComplexity($node)
    {
        $sum = '0';
        if ($node instanceof ASTConditionalExpression) {
            $sum = MathUtil::add($sum, $node->accept($this, 1));
        } elseif ($node instanceof ASTBooleanAndExpression
            || $node instanceof ASTBooleanOrExpression
            || $node instanceof ASTLogicalAndExpression
            || $node instanceof ASTLogicalOrExpression
            || $node instanceof ASTLogicalXorExpression
        ) {
            $sum = MathUtil::add($sum, '1');
        } else {
            foreach ($node->getChildren() as $child) {
                $expr = $this->sumComplexity($child);
                $sum  = MathUtil::add($sum, $expr);
            }
        }
        return $sum;
    }
}
