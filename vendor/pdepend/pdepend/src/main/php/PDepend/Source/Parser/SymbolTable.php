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

namespace PDepend\Source\Parser;

/**
 * This class provides a simple hashmap for name mappings done by the parser.
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class SymbolTable
{
    /**
     * Stack with all active scopes.
     *
     * @var array(array)
     */
    private $scopeStack = array();

    /**
     * The currently active scope.
     *
     * @var array(string=>string)
     */
    private $scope = array();

    /**
     * This method creates a new scope.
     *
     * @return void
     */
    public function createScope()
    {
        // Add copy of last scope as new scope
        array_push($this->scopeStack, $this->scope);
    }

    /**
     * This method destorys the top most scope.
     *
     * @return void
     */
    public function destroyScope()
    {
        // Remove scope from stack
        array_pop($this->scopeStack);

        // Update current scope to latest in stack
        $this->scope = end($this->scopeStack);
    }

    /**
     * Adds a new value to the top most scope.
     *
     * @param  string $key   The key of this scope value.
     * @param  mixed  $value A new scope value.
     * @return void
     */
    public function add($key, $value)
    {
        if (is_array($this->scope) === false) {
            throw new UnderflowException('No active scope.');
        }
        $this->scope[strtolower($key)] = $value;
    }

    /**
     * Resets the current scope
     *
     * @return void
     */
    public function resetScope()
    {
        if (is_array($this->scope) === false) {
            throw new UnderflowException('No active scope.');
        }
        $this->scope = array();
    }

    /**
     * This method will return the registered value for the given key, when it
     * exists in the current scope. The returned value will <b>null</b> if no
     * value exists for the given key.
     *
     * @param  string $key The key for a searched scope value.
     * @return mixed
     */
    public function lookup($key)
    {
        if (is_array($this->scope) === false) {
            throw new UnderflowException('No active scope.');
        }

        $key = strtolower($key);
        if (isset($this->scope[$key])) {
            return $this->scope[$key];
        }
        return null;
    }
}
