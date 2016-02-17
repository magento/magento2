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

namespace PDepend\Metrics\Analyzer\CodeRankAnalyzer;

use PDepend\Source\AST\AbstractASTArtifact;
use PDepend\Source\AST\AbstractASTClassOrInterface;
use PDepend\Source\AST\ASTClass;
use PDepend\Source\AST\ASTInterface;
use PDepend\Source\ASTVisitor\AbstractASTVisitor;

/**
 * Collects class and namespace metrics based on inheritance.
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class InheritanceStrategy extends AbstractASTVisitor implements CodeRankStrategyI
{
    /**
     * All found nodes.
     *
     * @var array(string=>array)
     */
    private $nodes = array();

    /**
     * Returns the collected nodes.
     *
     * @return array(string=>array)
     */
    public function getCollectedNodes()
    {
        return $this->nodes;
    }

    /**
     * Visits a code class object.
     *
     * @param  \PDepend\Source\AST\ASTClass $class
     * @return void
     */
    public function visitClass(ASTClass $class)
    {
        $this->fireStartClass($class);
        $this->visitType($class);
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
        $this->fireStartInterface($interface);
        $this->visitType($interface);
        $this->fireEndInterface($interface);
    }

    /**
     * Generic visitor method for classes and interfaces. Both visit methods
     * delegate calls to this method.
     *
     * @param  \PDepend\Source\AST\AbstractASTClassOrInterface $type
     * @return void
     */
    protected function visitType(AbstractASTClassOrInterface $type)
    {
        $namespace = $type->getNamespace();

        $this->initNode($namespace);
        $this->initNode($type);

        foreach ($type->getDependencies() as $dependency) {
            $depPkg = $dependency->getNamespace();

            $this->initNode($dependency);
            $this->initNode($depPkg);

            $this->nodes[$type->getId()]['in'][] = $dependency->getId();
            $this->nodes[$dependency->getId()]['out'][] = $type->getId();

            // No self references
            if ($namespace !== $depPkg) {
                $this->nodes[$namespace->getId()]['in'][]     = $depPkg->getId();
                $this->nodes[$depPkg->getId()]['out'][] = $namespace->getId();
            }
        }
    }

    /**
     * Initializes the temporary node container for the given <b>$node</b>.
     *
     * @param  \PDepend\Source\AST\AbstractASTArtifact $node
     * @return void
     */
    protected function initNode(AbstractASTArtifact $node)
    {
        if (!isset($this->nodes[$node->getId()])) {
            $this->nodes[$node->getId()] = array(
                'in'    =>  array(),
                'out'   =>  array(),
                'name'  =>  $node->getName(),
                'type'  =>  get_class($node)
            );
        }
    }
}
