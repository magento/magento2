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

/**
 * This class is a collection of concrete source analysis rules.
 *
 * @author    Manuel Pichler <mapi@phpmd.org>
 * @copyright 2008-2014 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class RuleSet implements \IteratorAggregate
{
    /**
     * Should this rule set force the strict mode.
     *
     * @var boolean
     * @since 1.2.0
     */
    private $strict = false;

    /**
     * The name of the file where this set is specified.
     *
     * @var string
     */
    private $fileName = '';

    /**
     * The name of this rule-set.
     *
     * @var string
     */
    private $name = '';

    /**
     * An optional description for this rule-set.
     *
     * @var string
     */
    private $description = '';

    /**
     * The violation report used by the rule-set.
     *
     * @var \PHPMD\Report
     */
    private $report;

    /**
     * Mapping between marker interfaces and concrete context code node classes.
     *
     * @var array(string=>string)
     */
    private $applyTo = array(
        'PHPMD\\Rule\\ClassAware'     => 'PHPMD\\Node\\ClassNode',
        'PHPMD\\Rule\\FunctionAware'  => 'PHPMD\\Node\\FunctionNode',
        'PHPMD\\Rule\\InterfaceAware' => 'PHPMD\\Node\\InterfaceNode',
        'PHPMD\\Rule\\MethodAware'    => 'PHPMD\\Node\\MethodNode',
    );

    /**
     * Mapping of rules that apply to a concrete code node type.
     *
     * @var array(string=>array)
     */
    private $rules = array(
        'PHPMD\\Node\\ClassNode'     =>  array(),
        'PHPMD\\Node\\FunctionNode'  =>  array(),
        'PHPMD\\Node\\InterfaceNode' =>  array(),
        'PHPMD\\Node\\MethodNode'    =>  array(),
    );

    /**
     * Returns the file name where the definition of this rule-set comes from.
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Sets the file name where the definition of this rule-set comes from.
     *
     * @param string $fileName The file name.
     *
     * @return void
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * Returns the name of this rule-set.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the name of this rule-set.
     *
     * @param string $name The name of this rule-set.
     *
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Returns the description text for this rule-set instance.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets the description text for this rule-set instance.
     *
     * @param string $description The description text.
     *
     * @return void
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Activates the strict mode for this rule set instance.
     *
     * @return void
     * @since 1.2.0
     */
    public function setStrict()
    {
        $this->strict = true;
    }

    /**
     * Returns the violation report used by the rule-set.
     *
     * @return \PHPMD\Report
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * Sets the violation report used by the rule-set.
     *
     * @param \PHPMD\Report $report
     * @return void
     */
    public function setReport(Report $report)
    {
        $this->report = $report;
    }

    /**
     * This method returns a rule by its name or <b>null</b> if it doesn't exist.
     *
     * @param string $name
     * @return \PHPMD\Rule
     */
    public function getRuleByName($name)
    {
        foreach ($this->getRules() as $rule) {
            if ($rule->getName() === $name) {
                return $rule;
            }
        }
        return null;
    }

    /**
     * This method returns an iterator will all rules that belong to this
     * rule-set.
     *
     * @return \Iterator
     */
    public function getRules()
    {
        $result = array();
        foreach ($this->rules as $rules) {
            foreach ($rules as $rule) {
                if (in_array($rule, $result, true) === false) {
                    $result[] = $rule;
                }
            }
        }

        return new \ArrayIterator($result);
    }

    /**
     * Adds a new rule to this rule-set.
     *
     * @param \PHPMD\Rule $rule
     * @return void
     */
    public function addRule(Rule $rule)
    {
        foreach ($this->applyTo as $applyTo => $type) {
            if ($rule instanceof $applyTo) {
                $this->rules[$type][] = $rule;
            }
        }
    }

    /**
     * Applies all registered rules that match against the concrete node type.
     *
     * @param \PHPMD\AbstractNode $node
     * @return void
     */
    public function apply(AbstractNode $node)
    {
        // Current node type
        $className = get_class($node);

        // Check for valid node type
        if (!isset($this->rules[$className])) {
            return;
        }

        // Apply all rules to this node
        foreach ($this->rules[$className] as $rule) {
            if ($node->hasSuppressWarningsAnnotationFor($rule) && !$this->strict) {
                continue;
            }
            $rule->setReport($this->report);
            $rule->apply($node);
        }
    }

    /**
     * Returns an iterator with all rules that are part of this rule-set.
     *
     * @return \Iterator
     */
    public function getIterator()
    {
        return $this->getRules();
    }
}
