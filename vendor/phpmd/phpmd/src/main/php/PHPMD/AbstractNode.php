<?php
/**
 * This file is part of PHPMD.
 *
 * Copyright (c) 2008-2012, Manuel Pichler <mapi@phpmd.org>.
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
 * @author    Manuel Pichler <mapi@phpmd.org>
 * @copyright 2008-2014 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace PHPMD;

use PHPMD\Node\ASTNode;

/**
 * This is an abstract base class for PHPMD code nodes, it is just a wrapper
 * around PDepend's object model.
 *
 * @author    Manuel Pichler <mapi@phpmd.org>
 * @copyright 2008-2014 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
abstract class AbstractNode
{
    /**
     *
     * @var \PDepend\Source\AST\ASTArtifact|\PDepend\Source\AST\ASTNode $node
     */
    private $node = null;

    /**
     * The collected metrics for this node.
     *
     * @var array(string=>mixed) $_metrics
     */
    private $metrics = null;

    /**
     * Constructs a new PHPMD node.
     *
     * @param \PDepend\Source\AST\ASTArtifact|\PDepend\Source\AST\ASTNode $node
     */
    public function __construct($node)
    {
        $this->node = $node;
    }

    /**
     * The magic call method is used to pipe requests from rules direct
     * to the underlying PDepend ast node.
     *
     * @param string $name
     * @param array $args
     * @return mixed
     * @throws \BadMethodCallException When the underlying PDepend node
     *         does not contain a method named <b>$name</b>.
     */
    public function __call($name, array $args)
    {
        if (method_exists($this->getNode(), $name)) {
            return call_user_func_array(array($this->getNode(), $name), $args);
        }
        throw new \BadMethodCallException(
            sprintf('Invalid method %s() called.', $name)
        );
    }

    /**
     * Returns the parent of this node or <b>null</b> when no parent node
     * exists.
     *
     * @return \PHPMD\AbstractNode
     */
    public function getParent()
    {
        if (($node = $this->node->getParent()) === null) {
            return null;
        }
        return new ASTNode($node, $this->getFileName());
    }

    /**
     * Returns a child node at the given index.
     *
     * @param integer $index The child offset.
     *
     * @return \PHPMD\Node\ASTNode
     */
    public function getChild($index)
    {
        return new ASTNode(
            $this->node->getChild($index),
            $this->getFileName()
        );
    }

    /**
     * Returns the first child of the given type or <b>null</b> when this node
     * has no child of the given type.
     *
     * @param string $type The searched child type.
     * @return \PHPMD\AbstractNode
     */
    public function getFirstChildOfType($type)
    {
        $node = $this->node->getFirstChildOfType('PDepend\Source\AST\AST' . $type);
        if ($node === null) {
            return null;
        }
        return new ASTNode($node, $this->getFileName());
    }

    /**
     * Searches recursive for all children of this node that are of the given
     * type.
     *
     * @param string $type The searched child type.
     * @return \PHPMD\AbstractNode[]
     */
    public function findChildrenOfType($type)
    {
        $children = $this->node->findChildrenOfType('PDepend\Source\AST\AST' . $type);

        $nodes = array();
        foreach ($children as $child) {
            $nodes[] = new ASTNode($child, $this->getFileName());
        }
        return $nodes;
    }

    /**
     * Tests if this node represents the the given type.
     *
     * @param string $type The expected node type.
     * @return boolean
     */
    public function isInstanceOf($type)
    {
        $class = 'PDepend\Source\AST\AST' . $type;
        return ($this->node instanceof $class);
    }

    /**
     * Returns the image of the underlying node.
     *
     * @return string
     */
    public function getImage()
    {
        return $this->node->getName();
    }

    /**
     * Returns the source name for this node, maybe a class or interface name,
     * or a package, method, function name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->node->getName();
    }

    /**
     * Returns the begin line for this node in the php source code file.
     *
     * @return integer
     */
    public function getBeginLine()
    {
        return $this->node->getStartLine();
    }

    /**
     * Returns the end line for this node in the php source code file.
     *
     * @return integer
     */
    public function getEndLine()
    {
        return $this->node->getEndLine();
    }

    /**
     * Returns the name of the declaring source file.
     *
     * @return string
     */
    public function getFileName()
    {
        return (string) $this->node->getCompilationUnit()->getFileName();
    }

    /**
     * Returns the wrapped PDepend node instance.
     *
     * @return \PDepend\Source\AST\ASTArtifact
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * Returns a textual representation/name for the concrete node type.
     *
     * @return string
     */
    public function getType()
    {
        $type = explode('\\', get_class($this));
        return preg_replace('(node$)', '', strtolower(array_pop($type)));
    }

    /**
     * This method will return the metric value for the given identifier or
     * <b>null</b> when no such metric exists.
     *
     * @param string $name The metric name or abbreviation.
     *
     * @return mixed
     */
    public function getMetric($name)
    {
        if (isset($this->metrics[$name])) {
            return $this->metrics[$name];
        }
        return null;
    }

    /**
     * This method will set the metrics for this node.
     *
     * @param array(string=>mixed) $metrics The collected node metrics.
     * @return void
     */
    public function setMetrics(array $metrics)
    {
        if ($this->metrics === null) {
            $this->metrics = $metrics;
        }
    }

    /**
     * Checks if this node has a suppressed annotation for the given rule
     * instance.
     *
     * @param \PHPMD\Rule $rule
     * @return boolean
     */
    abstract public function hasSuppressWarningsAnnotationFor(Rule $rule);

    /**
     * Returns the name of the parent type or <b>null</b> when this node has no
     * parent type.
     *
     * @return string
     */
    abstract public function getParentName();

    /**
     * Returns the name of the parent package.
     *
     * @return string
     */
    abstract public function getNamespaceName();
}
