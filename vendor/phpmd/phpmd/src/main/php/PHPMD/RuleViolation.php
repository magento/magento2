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

namespace PHPMD;

use PHPMD\Node\AbstractTypeNode;
use PHPMD\Node\FunctionNode;
use PHPMD\Node\MethodNode;

/**
 * This class is used as container for a single rule violation related to a source
 * node.
 *
 * @author    Manuel Pichler <mapi@phpmd.org>
 * @copyright 2008-2014 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class RuleViolation
{
    /**
     * The rule that causes this violation.
     *
     * @var \PHPMD\Rule
     */
    private $rule;

    /**
     * The context code node for this rule violation.
     *
     * @var \PHPMD\AbstractNode
     */
    private $node;

    /**
     * The description/message text that describes the violation.
     *
     * @var string
     */
    private $description;

    /**
     * The raw metric value which caused this rule violation.
     *
     * @var mixed
     */
    private $metric;

    /**
     * Name of the owning/context class or interface of this violation.
     *
     * @var string
     */
    private $className = null;

    /**
     * The name of a method or <b>null</b> when this violation has no method
     * context.
     *
     * @var string
     */
    private $methodName = null;

    /**
     * The name of a function or <b>null</b> when this violation has no function
     * context.
     *
     * @var string
     */
    private $functionName = null;

    /**
     * Constructs a new rule violation instance.
     *
     * @param \PHPMD\Rule $rule
     * @param \PHPMD\AbstractNode $node
     * @param string $violationMessage
     * @param mixed $metric
     */
    public function __construct(Rule $rule, AbstractNode $node, $violationMessage, $metric = null)
    {
        $this->rule        = $rule;
        $this->node        = $node;
        $this->metric      = $metric;
        $this->description = $violationMessage;

        if ($node instanceof AbstractTypeNode) {
            $this->className = $node->getName();
        } elseif ($node instanceof MethodNode) {
            $this->className  = $node->getParentName();
            $this->methodName = $node->getName();
        } elseif ($node instanceof FunctionNode) {
            $this->functionName = $node->getName();
        }
    }

    /**
     * Returns the rule that causes this violation.
     *
     * @return \PHPMD\Rule
     */
    public function getRule()
    {
        return $this->rule;
    }

    /**
     * Returns the description/message text that describes the violation.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns the raw metric value which caused this rule violation.
     *
     * @return mixed|null
     */
    public function getMetric()
    {
        return $this->metric;
    }

    /**
     * Returns the file name where this rule violation was detected.
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->node->getFileName();
    }

    /**
     * Returns the first line of the node that causes this rule violation.
     *
     * @return integer
     */
    public function getBeginLine()
    {
        return $this->node->getBeginLine();
    }

    /**
     * Returns the last line of the node that causes this rule violation.
     *
     * @return integer
     */
    public function getEndLine()
    {
        return $this->node->getEndLine();
    }

    /**
     * Returns the name of the package that contains this violation.
     *
     * @return string
     */
    public function getNamespaceName()
    {
        return $this->node->getNamespaceName();
    }

    /**
     * Returns the name of the parent class or interface or <b>null</b> when there
     * is no parent class.
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * Returns the name of a method or <b>null</b> when this violation has no
     * method context.
     *
     * @return string
     */
    public function getMethodName()
    {
        return $this->methodName;
    }

    /**
     * Returns the name of a function or <b>null</b> when this violation has no
     * function context.
     *
     * @return string
     */
    public function getFunctionName()
    {
        return $this->functionName;
    }
}
