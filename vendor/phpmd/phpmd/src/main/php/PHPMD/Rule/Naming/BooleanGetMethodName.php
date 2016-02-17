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
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace PHPMD\Rule\Naming;

use PHPMD\AbstractNode;
use PHPMD\AbstractRule;
use PHPMD\Node\MethodNode;
use PHPMD\Rule\MethodAware;

/**
 * This rule tests that a method which returns a boolean value does not start
 * with <b>get</b> or <b>_get</b> for a getter.
 *
 * @author    Manuel Pichler <mapi@phpmd.org>
 * @copyright 2008-2014 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class BooleanGetMethodName extends AbstractRule implements MethodAware
{
    /**
     * Extracts all variable and variable declarator nodes from the given node
     * and checks the variable name length against the configured minimum
     * length.
     *
     * @param \PHPMD\AbstractNode $node
     * @return void
     */
    public function apply(AbstractNode $node)
    {
        if ($this->isBooleanGetMethod($node)) {
            $this->addViolation($node, array($node->getImage()));
        }
    }

    /**
     * Tests if the given method matches all criteria to be an invalid
     * boolean get method.
     *
     * @param \PHPMD\Node\MethodNode $node
     * @return boolean
     */
    private function isBooleanGetMethod(MethodNode $node)
    {
        return $this->isGetterMethodName($node)
                && $this->isReturnTypeBoolean($node)
                && $this->isParameterizedOrIgnored($node);
    }

    /**
     * Tests if the given method starts with <b>get</b> or <b>_get</b>.
     *
     * @param \PHPMD\Node\MethodNode $node
     * @return boolean
     */
    private function isGetterMethodName(MethodNode $node)
    {
        return (preg_match('(^_?get)i', $node->getImage()) > 0);
    }

    /**
     * Tests if the given method is declared with return type boolean.
     *
     * @param \PHPMD\Node\MethodNode $node
     * @return boolean
     */
    private function isReturnTypeBoolean(MethodNode $node)
    {
        $comment = $node->getDocComment();
        return (preg_match('(\*\s*@return\s+bool(ean)?\s)i', $comment) > 0);
    }

    /**
     * Tests if the property <b>$checkParameterizedMethods</b> is set to <b>true</b>
     * or has no parameters.
     *
     * @param \PHPMD\Node\MethodNode $node
     * @return boolean
     */
    private function isParameterizedOrIgnored(MethodNode $node)
    {
        if ($this->getBooleanProperty('checkParameterizedMethods')) {
            return $node->getParameterCount() === 0;
        }
        return true;
    }
}
