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
 * @since 2.3
 */

namespace PDepend\Source\Language\PHP;

use PDepend\Source\Tokenizer\Tokens;

/**
 * Concrete parser implementation that supports features up to PHP version 7.0.
 *
 * TODO:
 * - Tokens: trait, callable, insteadof
 *   - allowed as
 *     - method
 *     - constant
 *   - not allowed as
 *     - class
 *     - interface
 *     - trait
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @since 2.3
 */
abstract class PHPParserVersion70 extends PHPParserVersion56
{
    /**
     * @param \PDepend\Source\AST\AbstractASTCallable $callable
     * @return \PDepend\Source\AST\AbstractASTCallable
     */
    protected function parseCallableDeclarationAddition($callable)
    {
        $this->consumeComments();
        if (Tokens::T_COLON != $this->tokenizer->peek()) {
            return $callable;
        }

        $this->consumeToken(Tokens::T_COLON);

        $type = $this->parseReturnTypeHint();
        $callable->addChild($type);

        return $callable;
    }

    /**
     * @return \PDepend\Source\AST\ASTType
     */
    protected function parseReturnTypeHint()
    {
        $this->consumeComments();

        switch ($tokenType = $this->tokenizer->peek()) {
            case Tokens::T_ARRAY:
                $type = $this->parseArrayType();
                break;
            case Tokens::T_SELF:
                $type = $this->parseSelfType();
                break;
            case Tokens::T_PARENT:
                $type = $this->parseParentType();
                break;
            default:
                $type = $this->parseTypeHint();
                break;
        }
        return $type;
    }

    /**
     * Parses a type hint that is valid in the supported PHP version.
     *
     * @return \PDepend\Source\AST\ASTNode
     * @since 2.3
     */
    protected function parseTypeHint()
    {
        switch ($this->tokenizer->peek()) {
            case Tokens::T_STRING:
            case Tokens::T_BACKSLASH:
            case Tokens::T_NAMESPACE:
                $name = $this->parseQualifiedName();

                if ($this->isScalarOrCallableTypeHint($name)) {
                    $type = $this->parseScalarOrCallableTypeHint($name);
                } else {
                    $type = $this->builder->buildAstClassOrInterfaceReference($name);
                }
                break;
            default:
                $type = parent::parseTypeHint();
                break;
        }
        return $type;
    }

    /**
     * Tests if the given image is a PHP 7 type hint.
     *
     * @param string $image
     * @return boolean
     */
    private function isScalarOrCallableTypeHint($image)
    {
        switch (strtolower($image)) {
            case 'int':
            case 'bool':
            case 'float':
            case 'string':
            case 'callable':
                return true;
        }

        return false;
    }

    /**
     * Parses a scalar type hint or a callable type hint.
     *
     * @param string $image
     * @return \PDepend\Source\AST\ASTType
     */
    private function parseScalarOrCallableTypeHint($image)
    {
        switch (strtolower($image)) {
            case 'int':
            case 'bool':
            case 'float':
            case 'string':
                return $this->builder->buildAstScalarType($image);
            case 'callable':
                return $this->builder->buildAstTypeCallable();
        }

        return false;
    }

    /**
     * This method will be called when the base parser cannot handle an expression
     * in the base version. In this method you can implement version specific
     * expressions.
     *
     * @return \PDepend\Source\AST\ASTNode
     * @throws \PDepend\Source\Parser\UnexpectedTokenException
     * @since 2.3
     */
    protected function parseOptionalExpressionForVersion()
    {
        if ($expression = $this->parseExpressionVersion70()) {
            return $expression;
        }
        return parent::parseOptionalExpressionForVersion();
    }

    /**
     * In this method we implement parsing of PHP 7.0 specific expressions.
     *
     * @return \PDepend\Source\AST\ASTNode
     * @since 2.3
     */
    protected function parseExpressionVersion70()
    {
        switch ($this->tokenizer->peek()) {
            case Tokens::T_SPACESHIP:
                $token = $this->consumeToken(Tokens::T_SPACESHIP);

                $expr = $this->builder->buildAstExpression();
                $expr->setImage($token->image);
                $expr->setStartLine($token->startLine);
                $expr->setStartColumn($token->startColumn);
                $expr->setEndLine($token->endLine);
                $expr->setEndColumn($token->endColumn);

                return $expr;
        }
    }
}
