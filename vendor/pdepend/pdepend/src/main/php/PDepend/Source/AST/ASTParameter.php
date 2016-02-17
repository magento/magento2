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
 * An instance of this class represents a function or method parameter within
 * the analyzed source code.
 *
 * <code>
 * <?php
 * class Builder
 * {
 *     public function buildNode($name, $line, \PDepend\Source\AST\ASTCompilationUnit $unit) {
 *     }
 * }
 *
 * function parse(Builder $builder, $file) {
 * }
 * </code>
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class ASTParameter extends AbstractASTArtifact
{
    /**
     * The parent function or method instance.
     *
     * @var \PDepend\Source\AST\AbstractASTCallable
     */
    private $declaringFunction = null;

    /**
     * The parameter position.
     *
     * @var integer
     */
    private $position = 0;

    /**
     * Is this parameter optional or mandatory?
     *
     * @var boolean
     */
    private $optional = false;

    /**
     * The wrapped formal parameter instance.
     *
     * @var \PDepend\Source\AST\ASTFormalParameter
     */
    private $formalParameter = null;

    /**
     * The wrapped variable declarator instance.
     *
     * @var \PDepend\Source\AST\ASTVariableDeclarator
     */
    private $variableDeclarator = null;

    /**
     * Constructs a new parameter instance for the given AST node.
     *
     * @param \PDepend\Source\AST\ASTFormalParameter $formalParameter
     */
    public function __construct(\PDepend\Source\AST\ASTFormalParameter $formalParameter)
    {
        $this->formalParameter    = $formalParameter;
        $this->variableDeclarator = $formalParameter->getFirstChildOfType(
            'PDepend\\Source\\AST\\ASTVariableDeclarator'
        );

        $this->id = spl_object_hash($this);
    }

    /**
     * Returns the item name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->variableDeclarator->getImage();
    }

    /**
     * Returns the line number where the item declaration can be found.
     *
     * @return integer
     */
    public function getStartLine()
    {
        return $this->formalParameter->getStartLine();
    }

    /**
     * Returns the line number where the item declaration ends.
     *
     * @return integer The last source line for this item.
     */
    public function getEndLine()
    {
        return $this->formalParameter->getEndLine();
    }

    /**
     * Returns the parent function or method instance or <b>null</b>
     *
     * @return \PDepend\Source\AST\AbstractASTCallable
     * @since  0.9.5
     */
    public function getDeclaringFunction()
    {
        return $this->declaringFunction;
    }

    /**
     * Sets the parent function or method object.
     *
     * @param  \PDepend\Source\AST\AbstractASTCallable $function
     * @return void
     * @since  0.9.5
     */
    public function setDeclaringFunction(AbstractASTCallable $function)
    {
        $this->declaringFunction = $function;
    }

    /**
     * This method will return the class where the parent method was declared.
     * The returned value will be <b>null</b> if the parent is a function.
     *
     * @return \PDepend\Source\AST\AbstractASTClassOrInterface
     * @since  0.9.5
     */
    public function getDeclaringClass()
    {
        // TODO: Review this for refactoring, maybe create a empty getParent()?
        if ($this->declaringFunction instanceof ASTMethod) {
            return $this->declaringFunction->getParent();
        }
        return null;
    }

    /**
     * Returns the parameter position in the method/function signature.
     *
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Sets the parameter position in the method/function signature.
     *
     * @param integer $position The parameter position.
     *
     * @return void
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * Returns the class type of this parameter. This method will return
     * <b>null</b> for all scalar type, only classes or interfaces are used.
     *
     * @return \PDepend\Source\AST\AbstractASTClassOrInterface
     * @since  0.9.5
     */
    public function getClass()
    {
        $classReference = $this->formalParameter->getFirstChildOfType(
            'PDepend\\Source\\AST\\ASTClassOrInterfaceReference'
        );
        if ($classReference === null) {
            return null;
        }
        return $classReference->getType();
    }

    /**
     * This method will return <b>true</b> when the parameter is passed by
     * reference.
     *
     * @return boolean
     * @since  0.9.5
     */
    public function isPassedByReference()
    {
        return $this->formalParameter->isPassedByReference();
    }

    /**
     * This method will return <b>true</b> when the parameter was declared with
     * the array type hint, otherwise the it will return <b>false</b>.
     *
     * @return boolean
     * @since  0.9.5
     */
    public function isArray()
    {
        $node = $this->formalParameter->getChild(0);
        return ($node instanceof \PDepend\Source\AST\ASTTypeArray);
    }

    /**
     * This method will return <b>true</b> when current parameter is a simple
     * scalar or it is an <b>array</b> or type explicit declared with a default
     * value <b>null</b>.
     *
     * @return boolean
     * @since  0.9.5
     */
    public function allowsNull()
    {
        return (
            (
                $this->isArray() === false
                && $this->getClass() === null
            ) || (
                $this->isDefaultValueAvailable() === true
                && $this->getDefaultValue() === null
            )
        );
    }

    /**
     * This method will return <b>true</b> when this parameter is optional and
     * can be left blank on invocation.
     *
     * @return boolean
     * @since  0.9.5
     */
    public function isOptional()
    {
        return $this->optional;
    }

    /**
     * This method can be used to mark a parameter optional. Note that a
     * parameter is only optional when it has a default value an no following
     * parameter has no default value.
     *
     * @param boolean $optional Boolean flag that marks this parameter a
     *                          optional or not.
     *
     * @return void
     * @since  0.9.5
     */
    public function setOptional($optional)
    {
        $this->optional = (boolean) $optional;
    }

    /**
     * This method will return <b>true</b> when the parameter declaration
     * contains a default value.
     *
     * @return boolean
     * @since  0.9.5
     */
    public function isDefaultValueAvailable()
    {
        $value = $this->variableDeclarator->getValue();
        if ($value === null) {
            return false;
        }
        return $value->isValueAvailable();
    }

    /**
     * This method will return the declared default value for this parameter.
     * Please note that this method will return <b>null</b> when no default
     * value was declared, therefore you should combine calls to this method and
     * {@link \PDepend\Source\AST\ASTParameter::isDefaultValueAvailable()} to
     * detect a NULL-value.
     *
     * @return mixed
     * @since  0.9.5
     */
    public function getDefaultValue()
    {
        $value = $this->variableDeclarator->getValue();
        if ($value === null) {
            return null;
        }
        return $value->getValue();
    }

    /**
     * ASTVisitor method for node tree traversal.
     *
     * @param  \PDepend\Source\ASTVisitor\ASTVisitor $visitor
     * @return void
     */
    public function accept(ASTVisitor $visitor)
    {
        $visitor->visitParameter($this);
    }

    /**
     * This method returns a string representation of this parameter.
     *
     * @return string
     */
    public function __toString()
    {
        $required  = $this->isOptional() ? 'optional' : 'required';
        $reference = $this->isPassedByReference() ? '&' : '';

        $typeHint = '';
        if ($this->isArray() === true) {
            $typeHint = ' array';
        } elseif ($this->getClass() !== null) {
            $typeHint = ' ' . $this->getClass()->getName();
        }

        $default = '';
        if ($this->isDefaultValueAvailable()) {
            $default = ' = ';

            $value = $this->getDefaultValue();
            if ($value === null) {
                $default  .= 'NULL';
                $typeHint .= ($typeHint !== '' ? ' or NULL' : '');
            } elseif ($value === false) {
                $default .= 'false';
            } elseif ($value === true) {
                $default .= 'true';
            } elseif (is_array($value) === true) {
                $default .= 'Array';
            } elseif (is_string($value) === true) {
                $default .= "'" . $value . "'";
            } else {
                $default .= $value;
            }
        }

        return sprintf(
            'Parameter #%d [ <%s>%s %s%s%s ]',
            $this->position,
            $required,
            $typeHint,
            $reference,
            $this->getName(),
            $default
        );
    }
}
