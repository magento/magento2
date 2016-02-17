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

namespace PHPMD\Rule\CleanCode;

use PDepend\Source\AST\ASTClassOrInterfaceReference;
use PDepend\Source\AST\ASTMethodPostfix;
use PDepend\Source\AST\ASTParentReference;
use PDepend\Source\AST\ASTSelfReference;
use PHPMD\AbstractNode;
use PHPMD\AbstractRule;
use PHPMD\Rule\FunctionAware;
use PHPMD\Rule\MethodAware;

/**
 * Check if static access is used in a method.
 *
 * Static access is known to cause hard dependencies between classes
 * and is a bad practice.
 *
 * @author    Benjamin Eberlei <benjamin@qafoo.com>
 * @copyright 2008-2014 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class StaticAccess extends AbstractRule implements MethodAware, FunctionAware
{
    /**
     * Method checks for use of static access and warns about it.
     *
     * @param \PHPMD\AbstractNode $node
     * @return void
     */
    public function apply(AbstractNode $node)
    {
        $exceptions = $this->getExceptionsList();
        $nodes = $node->findChildrenOfType('MemberPrimaryPrefix');

        foreach ($nodes as $methodCall) {
            if (!$this->isStaticMethodCall($methodCall)) {
                continue;
            }

            $className = $methodCall->getChild(0)->getNode()->getImage();
            if (in_array($className, $exceptions)) {
                continue;
            }

            $this->addViolation($methodCall, array($className, $node->getName()));
        }
    }

    private function isStaticMethodCall($methodCall)
    {
        return $methodCall->getChild(0)->getNode() instanceof ASTClassOrInterfaceReference &&
               $methodCall->getChild(1)->getNode() instanceof ASTMethodPostfix &&
               !$this->isCallingParent($methodCall) &&
               !$this->isCallingSelf($methodCall);
    }

    private function isCallingParent($methodCall)
    {
        return $methodCall->getChild(0)->getNode() instanceof ASTParentReference;
    }

    private function isCallingSelf($methodCall)
    {
        return $methodCall->getChild(0)->getNode() instanceof ASTSelfReference;
    }

    /**
     * Gets array of exceptions from property
     *
     * @return array
     */
    private function getExceptionsList()
    {
        try {
            $exceptions = $this->getStringProperty('exceptions');
        } catch (\OutOfBoundsException $e) {
            $exceptions = '';
        }

        return explode(',', $exceptions);
    }
}
