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
use PDepend\Metrics\AnalyzerFilterAware;
use PDepend\Metrics\AnalyzerNodeAware;
use PDepend\Metrics\AnalyzerProjectAware;
use PDepend\Source\AST\ASTArtifact;
use PDepend\Source\AST\ASTArtifactList;
use PDepend\Source\AST\ASTClass;
use PDepend\Source\AST\ASTFunction;
use PDepend\Source\AST\ASTInterface;
use PDepend\Source\AST\ASTMethod;
use PDepend\Source\AST\ASTNamespace;

/**
 * This analyzer calculates class/namespace hierarchy metrics.
 *
 * This analyzer expects that a node list filter is set, before it starts the
 * analyze process. This filter will suppress PHP internal and external library
 * stuff.
 *
 * This analyzer is based on the following metric set:
 * - http://www.aivosto.com/project/help/pm-oo-misc.html
 *
 * This analyzer is based on the following metric set:
 * - http://www.aivosto.com/project/help/pm-oo-misc.html
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class HierarchyAnalyzer extends AbstractAnalyzer implements AnalyzerFilterAware, AnalyzerNodeAware, AnalyzerProjectAware
{
    /**
     * Metrics provided by the analyzer implementation.
     */
    const M_NUMBER_OF_ABSTRACT_CLASSES = 'clsa',
          M_NUMBER_OF_CONCRETE_CLASSES = 'clsc',
          M_NUMBER_OF_ROOT_CLASSES     = 'roots',
          M_NUMBER_OF_LEAF_CLASSES     = 'leafs';

    /**
     * Number of all analyzed functions.
     *
     * @var integer
     */
    private $fcs = 0;

    /**
     * Number of all analyzer methods.
     *
     * @var integer
     */
    private $mts = 0;

    /**
     * Number of all analyzed classes.
     *
     * @var integer
     */
    private $cls = 0;

    /**
     * Number of all analyzed abstract classes.
     *
     * @var integer
     */
    private $clsa = 0;

    /**
     * Number of all analyzed interfaces.
     *
     * @var integer
     */
    private $interfs = 0;

    /**
     * Number of all root classes within the analyzed source code.
     *
     * @var array(string=>boolean)
     */
    private $roots = array();

    /**
     * Number of all none leaf classes within the analyzed source code
     *
     * @var array(string=>boolean)
     */
    private $noneLeafs = array();

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
     * @param  \PDepend\Source\AST\ASTNamespace[] $namespaces
     * @return void
     */
    public function analyze($namespaces)
    {
        if ($this->nodeMetrics === null) {
            $this->fireStartAnalyzer();

            // Init node metrics
            $this->nodeMetrics = array();

            // Visit all nodes
            foreach ($namespaces as $namespace) {
                $namespace->accept($this);
            }

            $this->fireEndAnalyzer();
        }
    }

    /**
     * Provides the project summary metrics as an <b>array</b>.
     *
     * @return array(string=>mixed)
     */
    public function getProjectMetrics()
    {
        // Count none leaf classes
        $noneLeafs = count($this->noneLeafs);

        return array(
            self::M_NUMBER_OF_ABSTRACT_CLASSES  =>  $this->clsa,
            self::M_NUMBER_OF_CONCRETE_CLASSES  =>  $this->cls - $this->clsa,
            self::M_NUMBER_OF_ROOT_CLASSES      =>  count($this->roots),
            self::M_NUMBER_OF_LEAF_CLASSES      =>  $this->cls - $noneLeafs,
        );
    }

    /**
     * This method will return an <b>array</b> with all generated metric values
     * for the given <b>$node</b> instance. If there are no metrics for the
     * requested node, this method will return an empty <b>array</b>.
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
     * Calculates metrics for the given <b>$class</b> instance.
     *
     * @param  \PDepend\Source\AST\ASTClass $class
     * @return void
     */
    public function visitClass(ASTClass $class)
    {
        if (false === $class->isUserDefined()) {
            return;
        }

        $this->fireStartClass($class);

        ++$this->cls;

        if ($class->isAbstract()) {
            ++$this->clsa;
        }

        $parentClass = $class->getParentClass();
        if ($parentClass !== null) {
            if ($parentClass->getParentClass() === null) {
                $this->roots[$parentClass->getId()] = true;
            }
            $this->noneLeafs[$parentClass->getId()] = true;
        }

        // Store node metric
        $this->nodeMetrics[$class->getId()] = array();

        foreach ($class->getMethods() as $method) {
            $method->accept($this);
        }
        foreach ($class->getProperties() as $property) {
            $property->accept($this);
        }

        $this->fireEndClass($class);
    }

    /**
     * Calculates metrics for the given <b>$function</b> instance.
     *
     * @param  \PDepend\Source\AST\ASTFunction $function
     * @return void
     */
    public function visitFunction(ASTFunction $function)
    {
        $this->fireStartFunction($function);
        ++$this->fcs;
        $this->fireEndFunction($function);
    }

    /**
     * Calculates metrics for the given <b>$interface</b> instance.
     *
     * @param  \PDepend\Source\AST\ASTInterface $interface
     * @return void
     */
    public function visitInterface(ASTInterface $interface)
    {
        $this->fireStartInterface($interface);

        ++$this->interfs;

        foreach ($interface->getMethods() as $method) {
            $method->accept($this);
        }

        $this->fireEndInterface($interface);
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
        ++$this->mts;
        $this->fireEndMethod($method);
    }

    /**
     * Calculates metrics for the given <b>$namespace</b> instance.
     *
     * @param  \PDepend\Source\AST\ASTNamespace $namespace
     * @return void
     */
    public function visitNamespace(ASTNamespace $namespace)
    {
        $this->fireStartNamespace($namespace);

        foreach ($namespace->getTypes() as $type) {
            $type->accept($this);
        }

        foreach ($namespace->getFunctions() as $function) {
            $function->accept($this);
        }

        $this->fireEndNamespace($namespace);
    }
}
