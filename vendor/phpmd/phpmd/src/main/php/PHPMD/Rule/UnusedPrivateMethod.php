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
use PHPMD\AbstractRule;
use PHPMD\Node\ASTNode;
use PHPMD\Node\ClassNode;
use PHPMD\Node\MethodNode;

/**
 * This rule collects all private methods in a class that aren't used in any
 * method of the analyzed class.
 *
 * @author    Manuel Pichler <mapi@phpmd.org>
 * @copyright 2008-2014 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class UnusedPrivateMethod extends AbstractRule implements ClassAware
{
    /**
     * This method checks that all private class methods are at least accessed
     * by one method.
     *
     * @param \PHPMD\AbstractNode $class
     * @return void
     */
    public function apply(AbstractNode $class)
    {
        foreach ($this->collectUnusedPrivateMethods($class) as $node) {
            $this->addViolation($node, array($node->getImage()));
        }
    }

    /**
     * This method collects all methods in the given class that are declared
     * as private and are not used in the same class' context.
     *
     * @param \PHPMD\Node\ClassNode $class
     * @return \PHPMD\AbstractNode[]
     */
    private function collectUnusedPrivateMethods(ClassNode $class)
    {
        $methods = $this->collectPrivateMethods($class);
        return $this->removeUsedMethods($class, $methods);
    }

    /**
     * Collects all private methods declared in the given class node.
     *
     * @param \PHPMD\Node\ClassNode $class
     * @return \PHPMD\AbstractNode[]
     */
    private function collectPrivateMethods(ClassNode $class)
    {
        $methods = array();
        foreach ($class->getMethods() as $method) {
            if ($this->acceptMethod($class, $method)) {
                $methods[strtolower($method->getImage())] = $method;
            }
        }
        return $methods;
    }

    /**
     * Returns <b>true</b> when the given method should be used for this rule's
     * analysis.
     *
     * @param \PHPMD\Node\ClassNode $class
     * @param \PHPMD\Node\MethodNode $method
     * @return boolean
     */
    private function acceptMethod(ClassNode $class, MethodNode $method)
    {
        return (
            $method->isPrivate() &&
            false === $method->hasSuppressWarningsAnnotationFor($this) &&
            strcasecmp($method->getImage(), $class->getImage()) !== 0 &&
            strcasecmp($method->getImage(), '__construct') !== 0 &&
            strcasecmp($method->getImage(), '__destruct') !== 0 &&
            strcasecmp($method->getImage(), '__clone') !== 0
        );
    }

    /**
     * This method removes all used methods from the given methods array.
     *
     * @param \PHPMD\Node\ClassNode $class
     * @param \PHPMD\Node\MethodNode[] $methods
     * @return \PHPMD\AbstractNode[]
     */
    private function removeUsedMethods(ClassNode $class, array $methods)
    {
        foreach ($class->findChildrenOfType('MethodPostfix') as $postfix) {
            if ($this->isClassScope($class, $postfix)) {
                unset($methods[strtolower($postfix->getImage())]);
            }
        }
        return $methods;
    }

    /**
     * This method checks that the given method postfix is accessed on an
     * instance or static reference to the given class.
     *
     * @param \PHPMD\Node\ClassNode $class
     * @param \PHPMD\Node\ASTNode $postfix
     * @return boolean
     */
    private function isClassScope(ClassNode $class, ASTNode $postfix)
    {
        $owner = $postfix->getParent()->getChild(0);
        return (
        $owner->isInstanceOf('MethodPostfix') ||
            $owner->isInstanceOf('SelfReference') ||
            $owner->isInstanceOf('StaticReference') ||
            strcasecmp($owner->getImage(), '$this') === 0 ||
            strcasecmp($owner->getImage(), $class->getImage()) === 0
        );
    }
}
