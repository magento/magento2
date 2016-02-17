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
use PDepend\Metrics\AnalyzerNodeAware;
use PDepend\Metrics\AnalyzerProjectAware;
use PDepend\Source\AST\AbstractASTCallable;
use PDepend\Source\AST\AbstractASTType;
use PDepend\Source\AST\ASTArtifact;
use PDepend\Source\AST\ASTArtifactList;
use PDepend\Source\AST\ASTClass;
use PDepend\Source\AST\ASTFunction;
use PDepend\Source\AST\ASTInterface;
use PDepend\Source\AST\ASTMethod;
use PDepend\Source\AST\ASTProperty;

/**
 * This analyzer collects coupling values for the hole project. It calculates
 * all function and method <b>calls</b> and the <b>fanout</b>, that means the
 * number of referenced types.
 *
 * The FANOUT calculation is based on the definition used by the apache maven
 * project.
 *
 * <ul>
 *   <li>field declarations (Uses doc comment annotations)</li>
 *   <li>formal parameters and return types (The return type uses doc comment
 *   annotations)</li>
 *   <li>throws declarations (Uses doc comment annotations)</li>
 *   <li>local variables</li>
 * </ul>
 *
 * http://www.jajakarta.org/turbine/en/turbine/maven/reference/metrics.html
 *
 * The implemented algorithm counts each type only once for a method and function.
 * Any type that is either a supertype or a subtype of the class is not counted.
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class CouplingAnalyzer extends AbstractAnalyzer implements AnalyzerNodeAware, AnalyzerProjectAware
{
    /**
     * Metrics provided by the analyzer implementation.
     */
    const M_CALLS  = 'calls',
          M_FANOUT = 'fanout',
          M_CA     = 'ca',
          M_CBO    = 'cbo',
          M_CE     = 'ce';

    /**
     * Has this analyzer already processed the source under test?
     *
     * @var   boolean
     * @since 0.10.2
     */
    private $uninitialized = true;

    /**
     * The number of method or function calls.
     *
     * @var integer
     */
    private $calls = 0;

    /**
     * Number of fanouts.
     *
     * @var integer
     */
    private $fanout = 0;

    /**
     * Temporary map that is used to hold the id combinations of dependee and
     * depender.
     *
     * @var   array(string=>array)
     * @since 0.10.2
     */
    private $dependencyMap = array();

    /**
     * This array holds a mapping between node identifiers and an array with
     * the node's metrics.
     *
     * @var   array(string=>array)
     * @since 0.10.2
     */
    private $nodeMetrics = array();

    /**
     * Provides the project summary as an <b>array</b>.
     *
     * <code>
     * array(
     *     'calls'   =>  23,
     *     'fanout'  =>  42
     * )
     * </code>
     *
     * @return array(string=>mixed)
     */
    public function getProjectMetrics()
    {
        return array(
            self::M_CALLS   =>  $this->calls,
            self::M_FANOUT  =>  $this->fanout
        );
    }

    /**
     * This method will return an <b>array</b> with all generated metric values
     * for the given node instance. If there are no metrics for the given node
     * this method will return an empty <b>array</b>.
     *
     * <code>
     * array(
     *     'loc'    =>  42,
     *     'ncloc'  =>  17,
     *     'cc'     =>  12
     * )
     * </code>
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
     * Processes all {@link \PDepend\Source\AST\ASTNamespace} code nodes.
     *
     * @param  \PDepend\Source\AST\ASTNamespace[] $namespaces
     * @return void
     */
    public function analyze($namespaces)
    {
        if ($this->uninitialized) {
            $this->doAnalyze($namespaces);
            $this->uninitialized = false;
        }
    }

    /**
     * This method traverses all namespaces in the given iterator and calculates
     * the coupling metrics for them.
     *
     * @param  \PDepend\Source\AST\ASTNamespace[] $namespaces
     * @return void
     * @since  0.10.2
     */
    private function doAnalyze($namespaces)
    {
        $this->fireStartAnalyzer();
        $this->reset();

        foreach ($namespaces as $namespace) {
            $namespace->accept($this);
        }

        $this->postProcessTemporaryCouplingMap();
        $this->fireEndAnalyzer();
    }

    /**
     * This method resets all internal state variables before the analyzer can
     * start the object tree traversal.
     *
     * @return void
     * @since  0.10.2
     */
    private function reset()
    {
        $this->calls         = 0;
        $this->fanout        = 0;
        $this->nodeMetrics   = array();
        $this->dependencyMap = array();
    }

    /**
     * This method takes the temporary coupling map with node IDs and calculates
     * the concrete node metrics.
     *
     * @return void
     * @since  0.10.2
     */
    private function postProcessTemporaryCouplingMap()
    {
        foreach ($this->dependencyMap as $id => $metrics) {
            $afferentCoupling = count($metrics['ca']);
            $efferentCoupling = count($metrics['ce']);

            $this->nodeMetrics[$id] = array(
                self::M_CA   =>  $afferentCoupling,
                self::M_CBO  =>  $efferentCoupling,
                self::M_CE   =>  $efferentCoupling
            );

            $this->fanout += $efferentCoupling;
        }

        $this->dependencyMap = array();
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

        $fanouts = array();
        if (($type = $function->getReturnClass()) !== null) {
            $fanouts[] = $type;
            ++$this->fanout;
        }
        foreach ($function->getExceptionClasses() as $type) {
            if (in_array($type, $fanouts, true) === false) {
                $fanouts[] = $type;
                ++$this->fanout;
            }
        }
        foreach ($function->getDependencies() as $type) {
            if (in_array($type, $fanouts, true) === false) {
                $fanouts[] = $type;
                ++$this->fanout;
            }
        }

        foreach ($fanouts as $fanout) {
            $this->initDependencyMap($fanout);

            $this->dependencyMap[$fanout->getId()]['ca'][$function->getId()] = true;
        }

        $this->countCalls($function);

        $this->fireEndFunction($function);
    }

    /**
     * Visit method for classes that will be called by PDepend during the
     * analysis phase with the current context class.
     *
     * @param  \PDepend\Source\AST\ASTClass $class
     * @return void
     * @since  0.10.2
     */
    public function visitClass(ASTClass $class)
    {
        $this->initDependencyMap($class);
        return parent::visitClass($class);
    }

    /**
     * Visit method for interfaces that will be called by PDepend during the
     * analysis phase with the current context interface.
     *
     * @param  \PDepend\Source\AST\ASTInterface $interface
     * @return void
     * @since  0.10.2
     */
    public function visitInterface(ASTInterface $interface)
    {
        $this->initDependencyMap($interface);
        return parent::visitInterface($interface);
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

        $declaringClass = $method->getParent();

        $this->calculateCoupling(
            $declaringClass,
            $method->getReturnClass()
        );

        foreach ($method->getExceptionClasses() as $type) {
            $this->calculateCoupling($declaringClass, $type);
        }
        foreach ($method->getDependencies() as $type) {
            $this->calculateCoupling($declaringClass, $type);
        }

        $this->countCalls($method);

        $this->fireEndMethod($method);
    }

    /**
     * Visits a property node.
     *
     * @param  \PDepend\Source\AST\ASTProperty $property
     * @return void
     */
    public function visitProperty(ASTProperty $property)
    {
        $this->fireStartProperty($property);

        $this->calculateCoupling(
            $property->getDeclaringClass(),
            $property->getClass()
        );

        $this->fireEndProperty($property);
    }

    /**
     * Calculates the coupling between the given types.
     *
     * @param  \PDepend\Source\AST\AbstractASTType $declaringType
     * @param  \PDepend\Source\AST\AbstractASTType $coupledType
     * @return void
     * @since  0.10.2
     */
    private function calculateCoupling(
        AbstractASTType $declaringType,
        AbstractASTType $coupledType = null
    ) {
        $this->initDependencyMap($declaringType);

        if (null === $coupledType) {
            return;
        }
        if ($coupledType->isSubtypeOf($declaringType)
            || $declaringType->isSubtypeOf($coupledType)
        ) {
            return;
        }

        $this->initDependencyMap($coupledType);

        $this->dependencyMap[
            $declaringType->getId()
        ]['ce'][
            $coupledType->getId()
        ] = true;

        $this->dependencyMap[
            $coupledType->getId()
        ]['ca'][
            $declaringType->getId()
        ] = true;
    }

    /**
     * This method will initialize a temporary coupling container for the given
     * given class or interface instance.
     *
     * @param  \PDepend\Source\AST\AbstractASTType $type
     * @return void
     * @since  0.10.2
     */
    private function initDependencyMap(AbstractASTType $type)
    {
        if (isset($this->dependencyMap[$type->getId()])) {
            return;
        }

        $this->dependencyMap[$type->getId()] = array(
            'ce' => array(),
            'ca' => array()
        );
    }

    /**
     * Counts all calls within the given <b>$callable</b>
     *
     * @param  \PDepend\Source\AST\AbstractASTCallable $callable
     * @return void
     */
    private function countCalls(AbstractASTCallable $callable)
    {
        $invocations = $callable->findChildrenOfType('PDepend\\Source\\AST\\ASTInvocation');

        $invoked = array();

        foreach ($invocations as $invocation) {
            $parents = $invocation->getParentsOfType('PDepend\\Source\\AST\\ASTMemberPrimaryPrefix');

            $image = '';
            foreach ($parents as $parent) {
                $child = $parent->getChild(0);
                if ($child !== $invocation) {
                    $image .= $child->getImage() . '.';
                }
            }
            $image .= $invocation->getImage() . '()';

            $invoked[$image] = $image;
        }

        $this->calls += count($invoked);
    }
}
