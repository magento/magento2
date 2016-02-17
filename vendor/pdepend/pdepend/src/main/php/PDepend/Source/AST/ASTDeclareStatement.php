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
 * @since 0.10.0
 */

namespace PDepend\Source\AST;

/**
 * This node class represents a declare-statement.
 *
 * <code>
 * -------------------------------
 * declare(encoding='ISO-8859-1');
 * -------------------------------
 *
 * -------------------
 * declare(ticks=42) {
 *     // ...
 * }
 * -
 *
 * ------------------
 * declare(ticks=42):
 *     // ...
 * enddeclare;
 * -----------
 * </code>
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @since 0.10.0
 */
class ASTDeclareStatement extends \PDepend\Source\AST\ASTStatement
{
    /**
     * The parsed declare values.
     *
     * @var \PDepend\Source\AST\ASTValue[]
     */
    protected $values = array();

    /**
     * Returns all values/parameters for this declare statement.
     *
     * @return \PDepend\Source\AST\ASTValue[]
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Adds a parameter/value for this declare-statement.
     *
     * @param string                       $name
     * @param \PDepend\Source\AST\ASTValue $value
     *
     * @return void
     */
    public function addValue($name, ASTValue $value)
    {
        $this->values[$name] = $value;
    }

    /**
     * Accept method of the visitor design pattern. This method will be called
     * by a visitor during tree traversal.
     *
     * @param \PDepend\Source\ASTVisitor\ASTVisitor $visitor
     * @param mixed                                 $data
     *
     * @return mixed
     * @since  0.10.0
     */
    public function accept(\PDepend\Source\ASTVisitor\ASTVisitor $visitor, $data = null)
    {
        return $visitor->visitDeclareStatement($this, $data);
    }

    /**
     * The magic sleep method will be called by PHP's runtime environment right
     * before an instance of this class gets serialized. It should return an
     * array with those property names that should be serialized for this class.
     *
     * @return array(string)
     * @since  0.10.0
     */
    public function __sleep()
    {
        return array_merge(array('values'), parent::__sleep());
    }
}
