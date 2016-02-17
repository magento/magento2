<?php
/**
 * This file is part of PHP Mess Detector.
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

namespace PHPMD\Node;

use PDepend\Source\AST\AbstractASTClassOrInterface;

/**
 * Abstract base class for classes and interfaces.
 *
 * @author    Manuel Pichler <mapi@phpmd.org>
 * @copyright 2008-2014 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
abstract class AbstractTypeNode extends AbstractNode
{
    /**
     * Constructs a new generic class or interface node.
     *
     * @param \PDepend\Source\AST\AbstractASTClassOrInterface $node
     */
    public function __construct(AbstractASTClassOrInterface $node)
    {
        parent::__construct($node);
    }

    /**
     * Returns an <b>array</b> with all methods defined in the context class or
     * interface.
     *
     * @return \PHPMD\Node\MethodNode[]
     */
    public function getMethods()
    {
        $methods = array();
        foreach ($this->getNode()->getMethods() as $method) {
            $methods[] = new MethodNode($method);
        }
        return $methods;
    }

    /**
     * Returns an array with the names of all methods within this class or
     * interface node.
     *
     * @return array(string)
     */
    public function getMethodNames()
    {
        $names = array();
        foreach ($this->getNode()->getMethods() as $method) {
            $names[] = $method->getName();
        }
        return $names;
    }

    /**
     * Returns the number of constants declared in this type.
     *
     * @return integer
     */
    public function getConstantCount()
    {
        return $this->getNode()->getConstants()->count();
    }

    /**
     * Returns the name of the parent namespace.
     *
     * @return string
     */
    public function getNamespaceName()
    {
        return $this->getNode()->getNamespace()->getName();
    }

    /**
     * Returns the name of the parent type or <b>null</b> when this node has no
     * parent type.
     *
     * @return string
     */
    public function getParentName()
    {
        return null;
    }
}
