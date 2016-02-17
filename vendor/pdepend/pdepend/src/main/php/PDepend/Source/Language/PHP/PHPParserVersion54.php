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

use PDepend\Source\AST\ASTArray;
use PDepend\Source\Parser\UnexpectedTokenException;
use PDepend\Source\Tokenizer\Tokens;

/**
 * Concrete parser implementation that supports features up to PHP version 5.4.
 *
 * TODO:
 * - Non constant operands are forbidden now in break and continue
 *   - break $var
 *   - continue $var
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @since 2.3
 */
abstract class PHPParserVersion54 extends PHPParserVersion53
{
    /**
     * Will return <b>true</b> if the given <b>$tokenType</b> is a valid class
     * name part.
     *
     * @param  integer $tokenType The type of a parsed token.
     * @return boolean
     * @since  0.10.6
     */
    protected function isClassName($tokenType)
    {
        switch ($tokenType) {
            case Tokens::T_NULL:
            case Tokens::T_TRUE:
            case Tokens::T_FALSE:
            case Tokens::T_STRING:
                return true;
        }
        return false;
    }

    /**
     * Tests if the give token is a valid function name in the supported PHP
     * version.
     *
     * @param integer $tokenType
     * @return boolean
     * @since 2.3
     */
    protected function isFunctionName($tokenType)
    {
        switch ($tokenType) {
            case Tokens::T_STRING:
            case Tokens::T_NULL:
            case Tokens::T_SELF:
            case Tokens::T_TRUE:
            case Tokens::T_FALSE:
            case Tokens::T_PARENT:
                return true;
        }
        return false;
    }

    /**
     * Tests if the given token type is a reserved keyword in the supported PHP
     * version.
     *
     * @param integer $tokenType
     * @return boolean
     */
    protected function isKeyword($tokenType)
    {
        switch ($tokenType) {
            case Tokens::T_CLASS:
            case Tokens::T_TRAIT:
            case Tokens::T_CALLABLE:
            case Tokens::T_INSTEADOF:
            case Tokens::T_INTERFACE:
                return true;
        }
        return false;
    }

    /**
     * Tests if the given token type is a valid type hint in the supported
     * PHP version.
     *
     * @param integer $tokenType
     * @return boolean
     * @since 1.0.0
     */
    protected function isTypeHint($tokenType)
    {
        switch ($tokenType) {
            case Tokens::T_CALLABLE:
                return true;
            default:
                return parent::isTypeHint($tokenType);
        }
    }

    /**
     * Parses a type hint that is valid in the supported PHP version.
     *
     * @return \PDepend\Source\AST\ASTNode
     * @since 1.0.0
     */
    protected function parseTypeHint()
    {
        switch ($this->tokenizer->peek()) {
            case Tokens::T_CALLABLE:
                $this->consumeToken(Tokens::T_CALLABLE);
                $type = $this->builder->buildAstTypeCallable();
                break;
            default:
                $type = parent::parseTypeHint();
                break;
        }
        return $type;
    }

    /**
     * Tests if the next token is a valid array start delimiter in the supported
     * PHP version.
     *
     * @return boolean
     * @since 1.0.0
     */
    protected function isArrayStartDelimiter()
    {
        switch ($this->tokenizer->peek()) {
            case Tokens::T_ARRAY:
            case Tokens::T_SQUARED_BRACKET_OPEN:
                return true;
        }
        return false;
    }

    /**
     * Parses a php array declaration.
     *
     * @param \PDepend\Source\AST\ASTArray $array
     * @param boolean $static
     * @return \PDepend\Source\AST\ASTArray
     * @since 1.0.0
     */
    protected function parseArray(ASTArray $array, $static = false)
    {
        switch ($this->tokenizer->peek()) {
            case Tokens::T_SQUARED_BRACKET_OPEN:
                $this->consumeToken(Tokens::T_SQUARED_BRACKET_OPEN);
                $this->parseArrayElements($array, Tokens::T_SQUARED_BRACKET_CLOSE, $static);
                $this->consumeToken(Tokens::T_SQUARED_BRACKET_CLOSE);
                break;
            default:
                parent::parseArray($array, $static);
                break;
        }
        return $array;
    }

    /**
     * Parses an integer value.
     *
     * @return \PDepend\Source\AST\ASTLiteral
     * @throws \PDepend\Source\Parser\UnexpectedTokenException
     */
    protected function parseIntegerNumber()
    {
        $token = $this->consumeToken(Tokens::T_LNUMBER);

        if ('0' !== substr($token->image, 0, 1)) {
            goto BUILD_LITERAL;
        }

        if (Tokens::T_STRING !== $this->tokenizer->peek()) {
            goto BUILD_LITERAL;
        }

        $token1 = $this->consumeToken(Tokens::T_STRING);
        if (0 === preg_match('(^b[01]+$)', $token1->image)) {
            throw new UnexpectedTokenException(
                $token1,
                $this->tokenizer->getSourceFile()
            );
        }

        $token->image = $token->image . $token1->image;
        $token->endLine = $token1->endLine;
        $token->endColumn = $token1->endColumn;

        BUILD_LITERAL:

        $literal = $this->builder->buildAstLiteral($token->image);
        $literal->configureLinesAndColumns(
            $token->startLine,
            $token->endLine,
            $token->startColumn,
            $token->endColumn
        );

        return $literal;
    }

    /**
     * Parses the class expr syntax supported since PHP 5.4.
     *
     * @return \PDepend\Source\AST\ASTNode
     * @since 2.3
     */
    protected function parsePostfixIdentifier()
    {
        switch ($this->tokenizer->peek()) {
            case Tokens::T_CURLY_BRACE_OPEN:
                $node = $this->parseCompoundExpression();
                break;
            default:
                $node = parent::parsePostfixIdentifier();
                break;
        }
        return $this->parseOptionalIndexExpression($node);
    }
}
