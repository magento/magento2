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
 * @since 0.9.12
 */

namespace PDepend\Source\AST;

/**
 * This node class represents a closure-expression.
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @since 0.9.12
 */
class ASTClosure extends ASTNode implements ASTCallable
{
    /**
     * @return \PDepend\Source\AST\ASTType
     */
    public function getReturnType()
    {
        foreach ($this->nodes as $node) {
            if ($node instanceof ASTType) {
                return $node;
            }
        }
        return null;
    }

    /**
     * This method will return <b>true</b> when this closure returns by
     * reference.
     *
     * @return boolean
     */
    public function returnsByReference()
    {
        return $this->getMetadataBoolean(5);
    }

    /**
     * This method can be used to flag this closure as returns by reference.
     *
     * @param boolean $returnsReference Does this closure return by reference?
     *
     * @return void
     */
    public function setReturnsByReference($returnsReference)
    {
        $this->setMetadataBoolean(5, (boolean) $returnsReference);
    }

    /**
     * Returns whether this closure was defined as static or not.
     *
     * This method will return <b>TRUE</b> when the closure was declared as
     * followed:
     *
     * <code>
     * $closure = static function( $e ) {
     *   return pow( $e, 2 );
     * }
     * </code>
     *
     * And it will return <b>FALSE</b> when we declare the closure as usual:
     *
     * <code>
     * $closure = function( $e ) {
     *   return pow( $e, 2 );
     * }
     * </code>
     *
     * @return boolean
     * @since  1.0.0
     */
    public function isStatic()
    {
        return $this->getMetadataBoolean(6);
    }

    /**
     * This method can be used to flag this closure instance as static.
     *
     * @param boolean $static Whether this closure is static or not.
     *
     * @return void
     * @since  1.0.0
     */
    public function setStatic($static)
    {
        $this->setMetadataBoolean(6, (boolean) $static);
    }

    /**
     * Accept method of the visitor design pattern. This method will be called
     * by a visitor during tree traversal.
     *
     * @param \PDepend\Source\ASTVisitor\ASTVisitor $visitor The calling visitor instance.
     * @param mixed                                 $data
     *
     * @return mixed
     * @since  0.9.12
     */
    public function accept(\PDepend\Source\ASTVisitor\ASTVisitor $visitor, $data = null)
    {
        return $visitor->visitClosure($this, $data);
    }

    /**
     * Returns the total number of the used property bag.
     *
     * @return integer
     * @since  1.0.0
     * @see    \PDepend\Source\AST\ASTNode#getMetadataSize()
     */
    protected function getMetadataSize()
    {
        return 7;
    }
}
