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
 * @since 0.9.6
 */

namespace PDepend\Source\AST;

use PDepend\Source\ASTVisitor\ASTVisitor;
use PDepend\Source\Builder\BuilderContext;

/**
 * This is a special reference container that is used whenever the keyword
 * <b>self</b> is used to reference a class or interface.
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @since 0.9.6
 */
class ASTSelfReference extends ASTClassOrInterfaceReference
{
    /**
     * The source image of this node.
     */
    const IMAGE = 'self';

    /**
     * The currently used builder context.
     *
     * @var   \PDepend\Source\Builder\BuilderContext
     * @since 0.10.0
     */
    protected $context = null;

    /**
     * The full qualified class name, including the namespace or namespace name.
     *
     * @var   string
     * @since 0.10.0
     * @todo  To reduce memory usage, move property into new metadata string
     */
    protected $qualifiedName = null;

    /**
     * Constructs a new type holder instance.
     *
     * @param \PDepend\Source\Builder\BuilderContext          $context
     * @param \PDepend\Source\AST\AbstractASTClassOrInterface
     */
    public function __construct(BuilderContext $context, AbstractASTClassOrInterface $target)
    {
        $this->context      = $context;
        $this->typeInstance = $target;
    }

    /**
     * Returns the visual representation for this node type.
     *
     * @return string
     * @since  0.10.4
     */
    public function getImage()
    {
        return self::IMAGE;
    }

    /**
     * Returns the class or interface instance that this node instance represents.
     *
     * @return \PDepend\Source\AST\AbstractASTClassOrInterface
     * @since  0.10.0
     */
    public function getType()
    {
        if ($this->typeInstance == null) {
            $this->typeInstance = $this->context
                ->getClassOrInterface($this->qualifiedName);
        }
        return $this->typeInstance;
    }

    /**
     * The magic sleep method will be called by PHP's runtime environment right
     * before an instance of this class gets serialized. It should return an
     * array with those property names that should be serialized for this class.
     *
     * @return array
     * @since  0.10.0
     */
    public function __sleep()
    {
        $this->qualifiedName = $this->getType()->getNamespaceName() . '\\' .
                               $this->getType()->getName();

        return array_merge(array('qualifiedName'), parent::__sleep());
    }

    /**
     * Accept method of the visitor design pattern. This method will be called
     * by a visitor during tree traversal.
     *
     * @param  \PDepend\Source\ASTVisitor\ASTVisitor $visitor
     * @param  mixed                                 $data
     * @return mixed
     * @since  0.9.12
     */
    public function accept(ASTVisitor $visitor, $data = null)
    {
        return $visitor->visitSelfReference($this, $data);
    }
}
