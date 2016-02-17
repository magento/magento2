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

namespace PDepend\Source\Parser;

use PDepend\Source\Tokenizer\Token;

/**
 * This class provides a scoped collection for {@link \PDepend\Source\Tokenizer\Token}
 * objects.
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @since 0.9.6
 */
class TokenStack
{
    /**
     * The actual token scope.
     *
     * @var \PDepend\Source\Tokenizer\Token[]
     */
    private $tokens = array();

    /**
     * Stack with token scopes.
     *
     * @var \PDepend\Source\Tokenizer\Token[][]
     */
    private $stack = array();

    /**
     * The current stack offset.
     *
     * @var integer
     */
    private $offset = 0;

    /**
     * This method will push a new token scope onto the stack,
     *
     * @return void
     */
    public function push()
    {
        $this->stack[$this->offset++] = $this->tokens;
        $this->tokens                  = array();
    }

    /**
     * This method will pop the top token scope from the stack and return an
     * array with all collected tokens. Additionally this method will add all
     * tokens of the removed scope onto the next token scope.
     *
     * @return \PDepend\Source\Tokenizer\Token[]
     */
    public function pop()
    {
        $tokens        = $this->tokens;
        $this->tokens = $this->stack[--$this->offset];

        unset($this->stack[$this->offset]);

        foreach ($tokens as $token) {
            $this->tokens[] = $token;
        }
        return $tokens;
    }

    /**
     * This method will add a new token to the currently active token scope.
     *
     * @param  \PDepend\Source\Tokenizer\Token $token The token to add.
     * @return \PDepend\Source\Tokenizer\Token
     */
    public function add(Token $token)
    {
        return ($this->tokens[] = $token);
    }
}
