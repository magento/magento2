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
 * @since      0.2.6
 */

namespace PHPMD\Rule;

use PHPMD\AbstractNode;
use PHPMD\AbstractRule;
use PHPMD\Node\ASTNode;

/**
 * Base class for rules that rely on local variables.
 *
 * @author    Manuel Pichler <mapi@phpmd.org>
 * @copyright 2008-2014 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 * @since     0.2.6
 */
abstract class AbstractLocalVariable extends AbstractRule
{
    /**
     * PHP super globals that are available in all php scopes, so that they
     * can never be unused local variables.
     *
     * @var array(string=>boolean)
     */
    private static $superGlobals = array(
        '$argc'                => true,
        '$argv'                => true,
        '$_COOKIE'             => true,
        '$_ENV'                => true,
        '$_FILES'              => true,
        '$_GET'                => true,
        '$_POST'               => true,
        '$_REQUEST'            => true,
        '$_SERVER'             => true,
        '$_SESSION'            => true,
        '$GLOBALS'             => true,
        '$HTTP_RAW_POST_DATA'  => true,
    );

    /**
     * Tests if the given variable node represents a local variable or if it is
     * a static object property or something similar.
     *
     * @param \PHPMD\Node\ASTNode $variable The variable to check.
     * @return boolean
     */
    protected function isLocal(ASTNode $variable)
    {
        return (false === $variable->isThis()
            && $this->isNotSuperGlobal($variable)
            && $this->isRegularVariable($variable)
        );
    }

    /**
     * Tests if the given variable represents one of the PHP super globals
     * that are available in scopes.
     *
     * @param \PHPMD\AbstractNode $variable
     * @return boolean
     */
    protected function isNotSuperGlobal(AbstractNode $variable)
    {
        return !isset(self::$superGlobals[$variable->getImage()]);
    }

    /**
     * Tests if the given variable node is a regular variable an not property
     * or method postfix.
     *
     * @param \PHPMD\Node\ASTNode $variable
     * @return boolean
     */
    protected function isRegularVariable(ASTNode $variable)
    {
        $node   = $this->stripWrappedIndexExpression($variable);
        $parent = $node->getParent();

        if ($parent->isInstanceOf('PropertyPostfix')) {
            $primaryPrefix = $parent->getParent();
            if ($primaryPrefix->getParent()->isInstanceOf('MemberPrimaryPrefix')) {
                return !$primaryPrefix->getParent()->isStatic();
            }
            return ($parent->getChild(0)->getNode() !== $node->getNode()
                || !$primaryPrefix->isStatic()
            );
        }
        return true;
    }

    /**
     * Removes all index expressions that are wrapped around the given node
     * instance.
     *
     * @param \PHPMD\Node\ASTNode $node
     * @return \PHPMD\Node\ASTNode
     */
    protected function stripWrappedIndexExpression(ASTNode $node)
    {
        if (false === $this->isWrappedByIndexExpression($node)) {
            return $node;
        }

        $parent = $node->getParent();
        if ($parent->getChild(0)->getNode() === $node->getNode()) {
            return $this->stripWrappedIndexExpression($parent);
        }
        return $node;
    }

    /**
     * Tests if the given variable node os part of an index expression.
     *
     * @param \PHPMD\Node\ASTNode $node
     * @return boolean
     */
    protected function isWrappedByIndexExpression(ASTNode $node)
    {
        return ($node->getParent()->isInstanceOf('ArrayIndexExpression')
            || $node->getParent()->isInstanceOf('StringIndexExpression')
        );
    }

    /**
     * PHP is case insensitive so we should compare function names case
     * insensitive.
     *
     * @param \PHPMD\AbstractNode $node
     * @param string $name
     * @return boolean
     */
    protected function isFunctionNameEqual(AbstractNode $node, $name)
    {
        return (0 === strcasecmp(trim($node->getImage(), '\\'), $name));
    }

    /**
     * AST puts namespace prefix to global functions called from a namespace.
     * This method checks if the last part of function fully qualified name is equal to $name
     *
     * @param \PHPMD\AbstractNode $node
     * @param string $name
     * @return boolean
     */
    protected function isFunctionNameEndingWith(AbstractNode $node, $name)
    {
        $parts = explode('\\', trim($node->getImage(), '\\'));

        return (0 === strcasecmp(array_pop($parts), $name));
    }
}
