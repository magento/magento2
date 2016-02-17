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

namespace PDepend\Source\Builder;

use PDepend\Source\AST\ASTClass;
use PDepend\Source\AST\ASTFunction;
use PDepend\Source\AST\ASTInterface;
use PDepend\Source\AST\ASTTrait;

/**
 * Base interface for a builder context.
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @since 0.10.0
 */
interface BuilderContext
{
    /**
     * This method can be used to register an existing function in the current
     * application context.
     *
     * @param  \PDepend\Source\AST\ASTFunction $function
     * @return void
     */
    public function registerFunction(ASTFunction $function);

    /**
     * This method can be used to register an existing trait in the current
     * class context.
     *
     * @param  \PDepend\Source\AST\ASTTrait $trait
     * @return void
     * @since  1.0.0
     */
    public function registerTrait(ASTTrait $trait);

    /**
     * This method can be used to register an existing class in the current
     * class context.
     *
     * @param  \PDepend\Source\AST\ASTClass $class
     * @return void
     */
    public function registerClass(ASTClass $class);

    /**
     * This method can be used to register an existing interface in the current
     * class context.
     *
     * @param  \PDepend\Source\AST\ASTInterface $interface
     * @return void
     */
    public function registerInterface(ASTInterface $interface);

    /**
     * Returns the trait instance for the given qualified name.
     *
     * @param  string $qualifiedName Full qualified trait name.
     * @return \PDepend\Source\AST\ASTTrait
     * @since  1.0.0
     */
    public function getTrait($qualifiedName);

    /**
     * Returns the class instance for the given qualified name.
     *
     * @param  string $qualifiedName
     * @return \PDepend\Source\AST\ASTClass
     */
    public function getClass($qualifiedName);

    /**
     * Returns a class or an interface instance for the given qualified name.
     *
     * @param  string $qualifiedName
     * @return \PDepend\Source\AST\AbstractASTClassOrInterface
     */
    public function getClassOrInterface($qualifiedName);
}
