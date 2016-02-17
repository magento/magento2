<?php
/**
 * This file is part of PHP Mess Detector.
 *
 * Copyright (c) 2008-2012, Manuel Pichler <mapi@phpmd.org>.
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
 * @author    Manuel Pichler <mapi@phpmd.org>
 * @copyright 2008-2014 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace PHPMD\Rule;

use PHPMD\AbstractNode;
use PHPMD\Node\MethodNode;

/**
 * This rule collects all formal parameters of a given function or method that
 * are not used in a statement of the artifact's body.
 *
 * @author    Manuel Pichler <mapi@phpmd.org>
 * @copyright 2008-2014 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class UnusedFormalParameter extends AbstractLocalVariable implements FunctionAware, MethodAware
{
    /**
     * Collected ast nodes.
     *
     * @var \PHPMD\Node\ASTNode[]
     */
    private $nodes = array();

    /**
     * This method checks that all parameters of a given function or method are
     * used at least one time within the artifacts body.
     *
     * @param \PHPMD\AbstractNode $node
     * @return void
     */
    public function apply(AbstractNode $node)
    {
        if ($this->isAbstractMethod($node)) {
            return;
        }

        // Magic methods should be ignored as invalid declarations are picked up by PHP.
        if ($this->isMagicMethod($node)) {
            return;
        }

        if ($this->isInheritedSignature($node)) {
            return;
        }

        if ($this->isNotDeclaration($node)) {
            return;
        }

        $this->nodes = array();

        $this->collectParameters($node);
        $this->removeUsedParameters($node);

        foreach ($this->nodes as $node) {
            $this->addViolation($node, array($node->getImage()));
        }
    }

    /**
     * Returns <b>true</b> when the given node is an abstract method.
     *
     * @param \PHPMD\AbstractNode $node
     * @return boolean
     */
    private function isAbstractMethod(AbstractNode $node)
    {
        if ($node instanceof MethodNode) {
            return $node->isAbstract();
        }
        return false;
    }

    /**
     * Returns <b>true</b> when the given node is method with signature declared as inherited using
     * {@inheritdoc} annotation.
     *
     * @param \PHPMD\AbstractNode $node
     * @return boolean
     */
    private function isInheritedSignature(AbstractNode $node)
    {
        if ($node instanceof MethodNode) {
            return preg_match('/\@inheritdoc/i', $node->getDocComment());
        }
        return false;
    }

    /**
     * Returns <b>true</b> when the given node is a magic method signature
     * @param AbstractNode $node
     * @return boolean
     */
    private function isMagicMethod(AbstractNode $node)
    {
        static $names = array(
                'call',
                'callStatic',
                'get',
                'set',
                'isset',
                'unset',
                'set_state'
        );

        if ($node instanceof MethodNode) {
            return preg_match('/\__(?:' . implode("|", $names) . ')/i', $node->getName());
        }
        return false;
    }

    /**
     * Tests if the given <b>$node</b> is a method and if this method is also
     * the initial declaration.
     *
     * @param \PHPMD\AbstractNode $node
     * @return boolean
     * @since 1.2.1
     */
    private function isNotDeclaration(AbstractNode $node)
    {
        if ($node instanceof MethodNode) {
            return !$node->isDeclaration();
        }
        return false;
    }

    /**
     * This method extracts all parameters for the given function or method node
     * and it stores the parameter images in the <b>$_images</b> property.
     *
     * @param \PHPMD\AbstractNode $node
     * @return void
     */
    private function collectParameters(AbstractNode $node)
    {
        // First collect the formal parameters container
        $parameters = $node->getFirstChildOfType('FormalParameters');

        // Now get all declarators in the formal parameters container
        $declarators = $parameters->findChildrenOfType('VariableDeclarator');

        foreach ($declarators as $declarator) {
            $this->nodes[$declarator->getImage()] = $declarator;
        }
    }

    /**
     * This method collects all local variables in the body of the currently
     * analyzed method or function and removes those parameters that are
     * referenced by one of the collected variables.
     *
     * @param \PHPMD\AbstractNode $node
     * @return void
     */
    private function removeUsedParameters(AbstractNode $node)
    {
        $variables = $node->findChildrenOfType('Variable');
        foreach ($variables as $variable) {
            if ($this->isRegularVariable($variable)) {
                unset($this->nodes[$variable->getImage()]);
            }
        }

        /* If the method calls func_get_args() then all parameters are
         * automatically referenced */
        $functionCalls = $node->findChildrenOfType('FunctionPostfix');
        foreach ($functionCalls as $functionCall) {
            if ($this->isFunctionNameEqual($functionCall, 'func_get_args')) {
                $this->nodes = array();
            }

            if ($this->isFunctionNameEndingWith($functionCall, 'compact')) {
                foreach ($functionCall->findChildrenOfType('Literal') as $literal) {
                    unset($this->nodes['$' . trim($literal->getImage(), '"\'')]);
                }
            }
        }
    }
}
