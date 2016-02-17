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

namespace PDepend\Source\AST;

use PDepend\Source\ASTVisitor\ASTVisitor;

/**
 * Representation of a code interface.
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class ASTInterface extends AbstractASTClassOrInterface
{
    /**
     * The modifiers for this interface instance, by default an interface is
     * always abstract.
     *
     * @var integer
     */
    protected $modifiers = State::IS_IMPLICIT_ABSTRACT;

    /**
     * Returns <b>true</b> if this is an abstract class or an interface.
     *
     * @return boolean
     */
    public function isAbstract()
    {
        return true;
    }

    /**
     * Sets a reference onto the parent class of this class node.
     *
     * @param  \PDepend\Source\AST\ASTClassReference
     * @return void
     * @throws \BadMethodCallException
     * @since  0.9.5
     */
    public function setParentClassReference(\PDepend\Source\AST\ASTClassReference $classReference)
    {
        throw new \BadMethodCallException(
            'Unsupported method ' . __METHOD__ . '() called.'
        );
    }

    /**
     * Checks that this user type is a subtype of the given <b>$type</b> instance.
     *
     * @param  \PDepend\Source\AST\AbstractASTType $type
     * @return boolean
     */
    public function isSubtypeOf(AbstractASTType $type)
    {
        if ($type === $this) {
            return true;
        } elseif ($type instanceof ASTInterface) {
            foreach ($this->getInterfaces() as $interface) {
                if ($interface === $type) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Returns the declared modifiers for this type.
     *
     * @return integer
     * @since  0.9.4
     */
    public function getModifiers()
    {
        return $this->modifiers;
    }

    /**
     * ASTVisitor method for node tree traversal.
     *
     * @param  \PDepend\Source\ASTVisitor\ASTVisitor $visitor
     * @return void
     */
    public function accept(ASTVisitor $visitor)
    {
        $visitor->visitInterface($this);
    }

    /**
     * The magic wakeup method will be called by PHP's runtime environment when
     * a serialized instance of this class was unserialized. This implementation
     * of the wakeup method will register this object in the the global class
     * context.
     *
     * @return void
     * @since  0.10.0
     */
    public function __wakeup()
    {
        parent::__wakeup();

        $this->context->registerInterface($this);
    }
}
