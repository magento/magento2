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

use PDepend\Source\AST\ASTMethod;
use PDepend\Source\AST\ASTClass;
use PDepend\Source\AST\ASTTrait;
use PHPMD\Rule;

/**
 * Wrapper around a PHP_Depend method node.
 *
 * @author    Manuel Pichler <mapi@phpmd.org>
 * @copyright 2008-2014 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class MethodNode extends AbstractCallableNode
{
    /**
     * Constructs a new method wrapper.
     *
     * @param \PDepend\Source\AST\ASTMethod $node
     */
    public function __construct(ASTMethod $node)
    {
        parent::__construct($node);
    }

    /**
     * Returns the name of the parent package.
     *
     * @return string
     */
    public function getNamespaceName()
    {
        return $this->getNode()->getParent()->getNamespace()->getName();
    }

    /**
     * Returns the name of the parent type or <b>null</b> when this node has no
     * parent type.
     *
     * @return string
     */
    public function getParentName()
    {
        return $this->getNode()->getParent()->getName();
    }

    /**
     * Returns <b>true</b> when the underlying method is declared as abstract or
     * is declared as child of an interface.
     *
     * @return boolean
     */
    public function isAbstract()
    {
        return $this->getNode()->isAbstract();
    }

    /**
     * Checks if this node has a suppressed annotation for the given rule
     * instance.
     *
     * @param \PHPMD\Rule $rule
     * @return boolean
     */
    public function hasSuppressWarningsAnnotationFor(Rule $rule)
    {
        if (parent::hasSuppressWarningsAnnotationFor($rule)) {
            return true;
        }
        return $this->getParentType()->hasSuppressWarningsAnnotationFor($rule);
    }

    /**
     * Returns the parent class or interface instance.
     *
     * @return \PHPMD\Node\AbstractTypeNode
     */
    public function getParentType()
    {
        $parentNode = $this->getNode()->getParent();

        if ($parentNode instanceof ASTTrait) {
            return new TraitNode($parentNode);
        }

        if ($parentNode instanceof ASTClass) {
            return new ClassNode($parentNode);
        }
        
        return new InterfaceNode($parentNode);
    }

    /**
     * Returns <b>true</b> when this method is the initial method declaration.
     * Otherwise this method will return <b>false</b>.
     *
     * @return boolean
     * @since 1.2.1
     */
    public function isDeclaration()
    {
        if ($this->isPrivate()) {
            return true;
        }

        $methodName = strtolower($this->getName());

        $parentNode = $this->getNode()->getParent();
        foreach ($parentNode->getInterfaces() as $parentType) {
            $methods = $parentType->getAllMethods();
            if (isset($methods[$methodName])) {
                return false;
            }
        }

        if (is_object($parentType = $parentNode->getParentClass())) {
            $methods = $parentType->getAllMethods();
            if (isset($methods[$methodName])) {
                return false;
            }
        }

        return true;
    }
}
