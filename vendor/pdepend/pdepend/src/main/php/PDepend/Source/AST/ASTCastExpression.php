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
 * @since 0.9.15
 */

namespace PDepend\Source\AST;

/**
 * This class represents a cast-expression node.
 *
 * <code>
 * //     ----------
 * $foo = (int) $bar;
 * //     ----------
 *
 * //     -----------
 * $foo = (bool) $bar;
 * //     -----------
 *
 * //     ------------
 * $foo = (array) $bar;
 * //     ------------
 *
 * //     ------------
 * $foo = (unset) $bar;
 * //     ------------
 *
 * //     -------------
 * $foo = (double) $bar;
 * //     -------------
 *
 * //     -------------
 * $foo = (string) $bar;
 * //     -------------
 *
 * //     -------------
 * $foo = (object) $bar;
 * //     -------------
 * </code>
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @since 0.9.15
 */
class ASTCastExpression extends \PDepend\Source\AST\ASTUnaryExpression
{
    /**
     * Constructs a new cast-expression node.
     *
     * @param string $image The original cast image.
     */
    public function __construct($image)
    {
        parent::__construct(preg_replace('(\s+)', '', strtolower($image)));
    }

    /**
     * Returns <b>true</b> when this node represents an array cast-expression.
     *
     * @return boolean
     */
    public function isArray()
    {
        return ($this->getImage() === '(array)');
    }

    /**
     * Returns <b>true</b> when this node represents an object cast-expression.
     *
     * @return boolean
     */
    public function isObject()
    {
        return ($this->getImage() === '(object)');
    }

    /**
     * Returns <b>true</b> when this node represents a boolean cast-expression.
     *
     * @return boolean
     */
    public function isBoolean()
    {
        return ($this->getImage() === '(bool)' || $this->getImage() === '(boolean)');
    }

    /**
     * Returns <b>true</b> when this node represents an integer cast-expression.
     *
     * @return boolean
     */
    public function isInteger()
    {
        return ($this->getImage() === '(int)' || $this->getImage() === '(integer)');
    }

    /**
     * Returns <b>true</b> when this node represents a float cast-expression.
     *
     * @return boolean
     */
    public function isFloat()
    {
        return ($this->getImage() === '(real)'
            || $this->getImage() === '(float)'
            || $this->getImage() === '(double)'
        );
    }

    /**
     * Returns <b>true</b> when this node represents a string cast-expression.
     *
     * @return boolean
     */
    public function isString()
    {
        return (strcmp('(string)', $this->getImage()) === 0);
    }

    /**
     * Returns <b>true</b> when this node represents an unset cast-expression.
     *
     * @return boolean
     */
    public function isUnset()
    {
        return ($this->getImage() === '(unset)');
    }

    /**
     * Accept method of the visitor design pattern. This method will be called
     * by a visitor during tree traversal.
     *
     * @param \PDepend\Source\ASTVisitor\ASTVisitor $visitor The calling visitor instance.
     * @param mixed                                 $data
     *
     * @return mixed
     */
    public function accept(\PDepend\Source\ASTVisitor\ASTVisitor $visitor, $data = null)
    {
        return $visitor->visitCastExpression($this, $data);
    }
}
