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
 * Concrete parser implementation that supports features up to PHP version 5.5.
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @since 2.3
 */
abstract class PHPParserVersion53 extends AbstractPHPParser
{

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
            case Tokens::T_ARRAY:
                $this->consumeToken(Tokens::T_ARRAY);
                $this->consumeComments();
                $this->consumeToken(Tokens::T_PARENTHESIS_OPEN);
                $this->parseArrayElements($array, Tokens::T_PARENTHESIS_CLOSE, $static);
                $this->consumeToken(Tokens::T_PARENTHESIS_CLOSE);
                break;
            default:
                throw new UnexpectedTokenException(
                    $this->tokenizer->next(),
                    $this->tokenizer->getSourceFile()
                );
        }
        return $array;
    }
}
