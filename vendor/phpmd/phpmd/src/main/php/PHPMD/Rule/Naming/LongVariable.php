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

namespace PHPMD\Rule\Naming;

use PHPMD\AbstractNode;
use PHPMD\AbstractRule;
use PHPMD\Rule\ClassAware;
use PHPMD\Rule\FunctionAware;
use PHPMD\Rule\MethodAware;

/**
 * This rule class will detect variables, parameters and properties with really
 * long names.
 *
 * @author    Manuel Pichler <mapi@phpmd.org>
 * @copyright 2008-2014 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class LongVariable extends AbstractRule implements ClassAware, MethodAware, FunctionAware
{
    /**
     * Temporary map holding variables that were already processed in the
     * current context.
     *
     * @var array(string=>boolean)
     */
    private $processedVariables = array();

    /**
     * Extracts all variable and variable declarator nodes from the given node
     * and checks the variable name length against the configured maximum
     * length.
     *
     * @param \PHPMD\AbstractNode $node
     * @return void
     */
    public function apply(AbstractNode $node)
    {
        $this->resetProcessed();

        if ($node->getType() === 'class') {
            $fields = $node->findChildrenOfType('FieldDeclaration');
            foreach ($fields as $field) {
                if ($field->isPrivate()) {
                    continue;
                }

                $declarators = $field->findChildrenOfType('VariableDeclarator');
                foreach ($declarators as $declarator) {
                    $this->checkNodeImage($declarator);
                }
            }
        } else {
            $declarators = $node->findChildrenOfType('VariableDeclarator');
            foreach ($declarators as $declarator) {
                $this->checkNodeImage($declarator);
            }

            $variables = $node->findChildrenOfType('Variable');
            foreach ($variables as $variable) {
                $this->checkNodeImage($variable);
            }
        }

        $this->resetProcessed();
    }

    /**
     * Checks if the variable name of the given node is smaller/equal to the
     * configured threshold.
     *
     * @param \PHPMD\AbstractNode $node
     * @return void
     */
    protected function checkNodeImage(AbstractNode $node)
    {
        if ($this->isNotProcessed($node)) {
            $this->addProcessed($node);
            $this->checkMaximumLength($node);
        }
    }

    /**
     * Template method that performs the real node image check.
     *
     * @param \PHPMD\AbstractNode $node
     * @return void
     */
    protected function checkMaximumLength(AbstractNode $node)
    {
        $threshold = $this->getIntProperty('maximum');
        if ($threshold >= strlen($node->getImage()) - 1) {
            return;
        }
        if ($this->isNameAllowedInContext($node)) {
            return;
        }
        $this->addViolation($node, array($node->getImage(), $threshold));
    }

    /**
     * Checks if a short name is acceptable in the current context. For the
     * moment the only context is a static member.
     *
     * @param \PHPMD\AbstractNode $node
     * @return boolean
     */
    private function isNameAllowedInContext(AbstractNode $node)
    {
        return $this->isChildOf($node, 'MemberPrimaryPrefix');
    }

    /**
     * Checks if the given node is a direct or indirect child of a node with
     * the given type.
     *
     * @param \PHPMD\AbstractNode $node
     * @param string $type
     * @return boolean
     */
    private function isChildOf(AbstractNode $node, $type)
    {
        $parent = $node->getParent();
        while (is_object($parent)) {
            if ($parent->isInstanceOf($type)) {
                return true;
            }
            $parent = $parent->getParent();
        }
        return false;
    }

    /**
     * Resets the already processed nodes.
     *
     * @return void
     */
    protected function resetProcessed()
    {
        $this->processedVariables = array();
    }

    /**
     * Flags the given node as already processed.
     *
     * @param \PHPMD\AbstractNode $node
     * @return void
     */
    protected function addProcessed(AbstractNode $node)
    {
        $this->processedVariables[$node->getImage()] = true;
    }

    /**
     * Checks if the given node was already processed.
     *
     * @param \PHPMD\AbstractNode $node
     * @return boolean
     */
    protected function isNotProcessed(AbstractNode $node)
    {
        return !isset($this->processedVariables[$node->getImage()]);
    }
}
