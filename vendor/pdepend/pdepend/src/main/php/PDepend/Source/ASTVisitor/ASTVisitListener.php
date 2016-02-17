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

namespace PDepend\Source\ASTVisitor;

use PDepend\Source\AST\ASTClass;
use PDepend\Source\AST\ASTCompilationUnit;
use PDepend\Source\AST\ASTFunction;
use PDepend\Source\AST\ASTInterface;
use PDepend\Source\AST\ASTMethod;
use PDepend\Source\AST\ASTNamespace;
use PDepend\Source\AST\ASTParameter;
use PDepend\Source\AST\ASTProperty;
use PDepend\Source\AST\ASTTrait;

/**
 * Base interface for a visitor listener.
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
interface ASTVisitListener
{
    /**
     * Is called when the visitor starts a new class instance.
     *
     * @param  \PDepend\Source\AST\ASTClass $class The context class instance.
     * @return void
     */
    public function startVisitClass(ASTClass $class);

    /**
     * Is called when the visitor ends with a class instance.
     *
     * @param  \PDepend\Source\AST\ASTClass $class The context class instance.
     * @return void
     */
    public function endVisitClass(ASTClass $class);

    /**
     * Is called when the visitor starts a new trait instance.
     *
     * @param  \PDepend\Source\AST\ASTTrait $trait
     * @return void
     * @since  1.0.0
     */
    public function startVisitTrait(ASTTrait $trait);

    /**
     * Is called when the visitor ends with a trait instance.
     *
     * @param  \PDepend\Source\AST\ASTTrait $trait
     * @return void
     * @since  1.0.0
     */
    public function endVisitTrait(ASTTrait $trait);

    /**
     * Is called when the visitor starts a new file instance.
     *
     * @param  \PDepend\Source\AST\ASTCompilationUnit $compilationUnit The context file instance.
     * @return void
     */
    public function startVisitFile(ASTCompilationUnit $compilationUnit);

    /**
     * Is called when the visitor ends with a file instance.
     *
     * @param  \PDepend\Source\AST\ASTCompilationUnit $compilationUnit The context file instance.
     * @return void
     */
    public function endVisitFile(ASTCompilationUnit $compilationUnit);

    /**
     * Is called when the visitor starts a new function instance.
     *
     * @param  \PDepend\Source\AST\ASTFunction $function
     * @return void
     */
    public function startVisitFunction(ASTFunction $function);

    /**
     * Is called when the visitor ends with a function instance.
     *
     * @param  ASTFunction $function
     * @return void
     */
    public function endVisitFunction(ASTFunction $function);

    /**
     * Is called when the visitor starts a new interface instance.
     *
     * @param  \PDepend\Source\AST\ASTInterface $interface
     * @return void
     */
    public function startVisitInterface(ASTInterface $interface);

    /**
     * Is called when the visitor ends with an interface instance.
     *
     * @param  \PDepend\Source\AST\ASTInterface $interface
     * @return void
     */
    public function endVisitInterface(ASTInterface $interface);

    /**
     * Is called when the visitor starts a new method instance.
     *
     * @param  \PDepend\Source\AST\ASTMethod $method
     * @return void
     */
    public function startVisitMethod(ASTMethod $method);

    /**
     * Is called when the visitor ends with a method instance.
     *
     * @param  \PDepend\Source\AST\ASTMethod $method
     * @return void
     */
    public function endVisitMethod(ASTMethod $method);

    /**
     * Is called when the visitor starts a new namespace instance.
     *
     * @param  \PDepend\Source\AST\ASTNamespace $namespace
     * @return void
     */
    public function startVisitNamespace(ASTNamespace $namespace);

    /**
     * Is called when the visitor ends with a namespace instance.
     *
     * @param  \PDepend\Source\AST\ASTNamespace $namespace
     * @return void
     */
    public function endVisitNamespace(ASTNamespace $namespace);

    /**
     * Is called when the visitor starts a new parameter instance.
     *
     * @param  \PDepend\Source\AST\ASTParameter $parameter
     * @return void
     */
    public function startVisitParameter(ASTParameter $parameter);

    /**
     * Is called when the visitor ends with a parameter instance.
     *
     * @param  \PDepend\Source\AST\ASTParameter $parameter
     * @return void
     */
    public function endVisitParameter(ASTParameter $parameter);

    /**
     * Is called when the visitor starts a new property instance.
     *
     * @param  \PDepend\Source\AST\ASTProperty $property
     * @return void
     */
    public function startVisitProperty(ASTProperty $property);

    /**
     * Is called when the visitor ends with a property instance.
     *
     * @param  \PDepend\Source\AST\ASTProperty $property
     * @return void
     */
    public function endVisitProperty(ASTProperty $property);
}
