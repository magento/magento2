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
use PDepend\Metrics\AggregateAnalyzer;
use PDepend\Metrics\AnalyzerFilterAware;
use PDepend\Metrics\AnalyzerNodeAware;
use PDepend\Source\AST\AbstractASTType;
use PDepend\Source\AST\ASTArtifact;
use PDepend\Source\AST\ASTArtifactList;
use PDepend\Source\AST\ASTClass;
use PDepend\Source\AST\ASTInterface;
use PDepend\Source\AST\ASTMethod;
use PDepend\Source\AST\ASTProperty;
use PDepend\Source\AST\ASTTrait;

/**
 * Generates some class level based metrics. This analyzer is based on the
 * metrics specified in the following document.
 *
 * http://www.aivosto.com/project/help/pm-oo-misc.html
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class ClassLevelAnalyzer extends AbstractAnalyzer implements AggregateAnalyzer, AnalyzerFilterAware, AnalyzerNodeAware
{
    /**
     * Metrics provided by the analyzer implementation.
     */
    const M_IMPLEMENTED_INTERFACES       = 'impl',
          M_CLASS_INTERFACE_SIZE         = 'cis',
          M_CLASS_SIZE                   = 'csz',
          M_NUMBER_OF_PUBLIC_METHODS     = 'npm',
          M_PROPERTIES                   = 'vars',
          M_PROPERTIES_INHERIT           = 'varsi',
          M_PROPERTIES_NON_PRIVATE       = 'varsnp',
          M_WEIGHTED_METHODS             = 'wmc',
          M_WEIGHTED_METHODS_INHERIT     = 'wmci',
          M_WEIGHTED_METHODS_NON_PRIVATE = 'wmcnp';

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
     * The internal used cyclomatic complexity analyzer.
     *
     * @var \PDepend\Metrics\Analyzer\CyclomaticComplexityAnalyzer
     */
    private $cyclomaticAnalyzer = null;

    /**
     * Processes all {@link \PDepend\Source\AST\ASTNamespace} code nodes.
     *
     * @param  \PDepend\Source\AST\ASTNamespace[] $namespaces
     * @return void
     */
    public function analyze($namespaces)
    {
        if ($this->nodeMetrics === null) {
            // First check for the require cc analyzer
            if ($this->cyclomaticAnalyzer === null) {
                throw new \RuntimeException('Missing required CC analyzer.');
            }

            $this->fireStartAnalyzer();

            $this->cyclomaticAnalyzer->analyze($namespaces);

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
     * This method must return an <b>array</b> of class names for required
     * analyzers.
     *
     * @return array(string)
     */
    public function getRequiredAnalyzers()
    {
        return array('PDepend\\Metrics\\Analyzer\\CyclomaticComplexityAnalyzer');
    }

    /**
     * Adds a required sub analyzer.
     *
     * @param  \PDepend\Metrics\Analyzer $analyzer The sub analyzer instance.
     * @return void
     */
    public function addAnalyzer(\PDepend\Metrics\Analyzer $analyzer)
    {
        if ($analyzer instanceof \PDepend\Metrics\Analyzer\CyclomaticComplexityAnalyzer) {
            $this->cyclomaticAnalyzer = $analyzer;
        } else {
            throw new \InvalidArgumentException('CC Analyzer required.');
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
        $metrics = array();
        if (isset($this->nodeMetrics[$artifact->getId()])) {
            $metrics = $this->nodeMetrics[$artifact->getId()];
        }
        return $metrics;
    }

    /**
     * Visits a class node.
     *
     * @param  \PDepend\Source\AST\ASTClass $class
     * @return void
     */
    public function visitClass(ASTClass $class)
    {
        $this->fireStartClass($class);

        $impl  = $class->getInterfaces()->count();
        $varsi = $this->calculateVarsi($class);
        $wmci  = $this->calculateWmciForClass($class);

        $this->nodeMetrics[$class->getId()] = array(
            self::M_IMPLEMENTED_INTERFACES       => $impl,
            self::M_CLASS_INTERFACE_SIZE         => 0,
            self::M_CLASS_SIZE                   => 0,
            self::M_NUMBER_OF_PUBLIC_METHODS     => 0,
            self::M_PROPERTIES                   => 0,
            self::M_PROPERTIES_INHERIT           => $varsi,
            self::M_PROPERTIES_NON_PRIVATE       => 0,
            self::M_WEIGHTED_METHODS             => 0,
            self::M_WEIGHTED_METHODS_INHERIT     => $wmci,
            self::M_WEIGHTED_METHODS_NON_PRIVATE => 0
        );

        foreach ($class->getProperties() as $property) {
            $property->accept($this);
        }
        foreach ($class->getMethods() as $method) {
            $method->accept($this);
        }

        $this->fireEndClass($class);
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
     * Visits a trait node.
     *
     * @param  \PDepend\Source\AST\ASTTrait $trait
     * @return void
     * @since  1.0.0
     */
    public function visitTrait(ASTTrait $trait)
    {
        $this->fireStartTrait($trait);

        $wmci = $this->calculateWmciForTrait($trait);

        $this->nodeMetrics[$trait->getId()] = array(
            self::M_IMPLEMENTED_INTERFACES       => 0,
            self::M_CLASS_INTERFACE_SIZE         => 0,
            self::M_CLASS_SIZE                   => 0,
            self::M_NUMBER_OF_PUBLIC_METHODS     => 0,
            self::M_PROPERTIES                   => 0,
            self::M_PROPERTIES_INHERIT           => 0,
            self::M_PROPERTIES_NON_PRIVATE       => 0,
            self::M_WEIGHTED_METHODS             => 0,
            self::M_WEIGHTED_METHODS_INHERIT     => $wmci,
            self::M_WEIGHTED_METHODS_NON_PRIVATE => 0
        );

        foreach ($trait->getProperties() as $property) {
            $property->accept($this);
        }
        foreach ($trait->getMethods() as $method) {
            $method->accept($this);
        }

        $this->fireEndTrait($trait);
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

        $id = $method->getParent()->getId();

        $ccn = $this->cyclomaticAnalyzer->getCcn2($method);

        // Increment Weighted Methods Per Class(WMC) value
        $this->nodeMetrics[$id][self::M_WEIGHTED_METHODS] += $ccn;
        // Increment Class Size(CSZ) value
        ++$this->nodeMetrics[$id][self::M_CLASS_SIZE];

        // Increment Non Private values
        if ($method->isPublic()) {
            ++$this->nodeMetrics[$id][self::M_NUMBER_OF_PUBLIC_METHODS];
            // Increment Non Private WMC value
            $this->nodeMetrics[$id][self::M_WEIGHTED_METHODS_NON_PRIVATE] += $ccn;
            // Increment Class Interface Size(CIS) value
            ++$this->nodeMetrics[$id][self::M_CLASS_INTERFACE_SIZE];
        }

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

        $id = $property->getDeclaringClass()->getId();

        // Increment VARS value
        ++$this->nodeMetrics[$id][self::M_PROPERTIES];
        // Increment Class Size(CSZ) value
        ++$this->nodeMetrics[$id][self::M_CLASS_SIZE];

        // Increment Non Private values
        if ($property->isPublic()) {
            // Increment Non Private VARS value
            ++$this->nodeMetrics[$id][self::M_PROPERTIES_NON_PRIVATE];
            // Increment Class Interface Size(CIS) value
            ++$this->nodeMetrics[$id][self::M_CLASS_INTERFACE_SIZE];
        }

        $this->fireEndProperty($property);
    }

    /**
     * Calculates the Variables Inheritance of a class metric, this method only
     * counts protected and public properties of parent classes.
     *
     * @param  \PDepend\Source\AST\ASTClass $class The context class instance.
     * @return integer
     */
    private function calculateVarsi(ASTClass $class)
    {
        // List of properties, this method only counts not overwritten properties
        $properties = array();
        // Collect all properties of the context class
        foreach ($class->getProperties() as $prop) {
            $properties[$prop->getName()] = true;
        }

        foreach ($class->getParentClasses() as $parent) {
            foreach ($parent->getProperties() as $prop) {
                if (!$prop->isPrivate() && !isset($properties[$prop->getName()])) {
                    $properties[$prop->getName()] = true;
                }
            }
        }
        return count($properties);
    }

    /**
     * Calculates the Weight Method Per Class metric, this method only counts
     * protected and public methods of parent classes.
     *
     * @param  \PDepend\Source\AST\ASTClass $class The context class instance.
     * @return integer
     */
    private function calculateWmciForClass(ASTClass $class)
    {
        $ccn = $this->calculateWmci($class);

        foreach ($class->getParentClasses() as $parent) {
            foreach ($parent->getMethods() as $method) {
                if ($method->isPrivate()) {
                    continue;
                }
                if (isset($ccn[($name = $method->getName())])) {
                    continue;
                }
                $ccn[$name] = $this->cyclomaticAnalyzer->getCcn2($method);
            }
        }

        return array_sum($ccn);
    }

    /**
     * Calculates the Weight Method Per Class metric for a trait.
     *
     * @param  \PDepend\Source\AST\ASTTrait $trait
     * @return integer
     * @since  1.0.6
     */
    private function calculateWmciForTrait(ASTTrait $trait)
    {
        return array_sum($this->calculateWmci($trait));
    }

    /**
     * Calculates the Weight Method Per Class metric.
     *
     * @param  \PDepend\Source\AST\AbstractASTType $type
     * @return integer[]
     * @since  1.0.6
     */
    private function calculateWmci(AbstractASTType $type)
    {
        $ccn = array();

        foreach ($type->getMethods() as $method) {
            $ccn[$method->getName()] = $this->cyclomaticAnalyzer->getCcn2($method);
        }

        return $ccn;
    }
}
