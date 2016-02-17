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
 * This is the abstract base class for pmd rules.
 *
 * @author    Manuel Pichler <mapi@phpmd.org>
 * @copyright 2008-2014 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 *
 * @SuppressWarnings(PHPMD)
 */
abstract class AbstractRule implements Rule
{
    /**
     * The name for this rule instance.
     *
     * @var string $_name
     */
    private $name = '';

    /**
     * The violation message text for this rule.
     *
     * @var string
     */
    private $message = '';

    /**
     * The version since when this rule is available.
     *
     * @var string
     */
    private $since = null;

    /**
     * An url will external information for this rule.
     *
     * @var string
     */
    private $externalInfoUrl = '';

    /**
     * An optional description for this rule.
     *
     * @var string
     */
    private $description = '';

    /**
     * A list of code examples for this rule.
     *
     * @var array(string)
     */
    private $examples = array();

    /**
     * The name of the parent rule-set instance.
     *
     * @var string
     */
    private $ruleSetName = '';

    /**
     * The priority of this rule.
     *
     * @var integer
     */
    private $priority = self::LOWEST_PRIORITY;

    /**
     * Configuration properties for this rule instance.
     *
     * @var array(string=>string)
     */
    private $properties = array();

    /**
     * The report for object for this rule.
     *
     * @var \PHPMD\Report
     */
    private $report = null;

    /**
     * Returns the name for this rule instance.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the name for this rule instance.
     *
     * @param string $name The rule name.
     *
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Returns the version since when this rule is available or <b>null</b>.
     *
     * @return string
     */
    public function getSince()
    {
        return $this->since;
    }

    /**
     * Sets the version since when this rule is available.
     *
     * @param string $since The version number.
     *
     * @return void
     */
    public function setSince($since)
    {
        $this->since = $since;
    }

    /**
     * Returns the violation message text for this rule.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Sets the violation message text for this rule.
     *
     * @param string $message The violation message
     *
     * @return void
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * Returns an url will external information for this rule.
     *
     * @return string
     */
    public function getExternalInfoUrl()
    {
        return $this->externalInfoUrl;
    }

    /**
     * Sets an url will external information for this rule.
     *
     * @param string $externalInfoUrl The info url.
     *
     * @return void
     */
    public function setExternalInfoUrl($externalInfoUrl)
    {
        $this->externalInfoUrl = $externalInfoUrl;
    }

    /**
     * Returns the description text for this rule instance.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets the description text for this rule instance.
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
     * Returns a list of examples for this rule.
     *
     * @return array(string)
     */
    public function getExamples()
    {
        return $this->examples;
    }

    /**
     * Adds a code example for this rule.
     *
     * @param string $example The code example.
     *
     * @return void
     */
    public function addExample($example)
    {
        $this->examples[] = $example;
    }

    /**
     * Returns the priority of this rule.
     *
     * @return integer
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set the priority of this rule.
     *
     * @param integer $priority The rule priority
     *
     * @return void
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    /**
     * Returns the name of the parent rule-set instance.
     *
     * @return string
     */
    public function getRuleSetName()
    {
        return $this->ruleSetName;
    }

    /**
     * Sets the name of the parent rule set instance.
     *
     * @param string $ruleSetName The rule-set name.
     *
     * @return void
     */
    public function setRuleSetName($ruleSetName)
    {
        $this->ruleSetName = $ruleSetName;
    }

    /**
     * Returns the violation report for this rule.
     *
     * @return \PHPMD\Report
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * Sets the violation report for this rule.
     *
     * @param \PHPMD\Report $report
     * @return void
     */
    public function setReport(Report $report)
    {
        $this->report = $report;
    }

    /**
     * Adds a configuration property to this rule instance.
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    public function addProperty($name, $value)
    {
        $this->properties[$name] = $value;
    }

    /**
     * Returns the value of a configured property as a boolean or throws an
     * exception when no property with <b>$name</b> exists.
     *
     * @param string $name
     * @return boolean
     * @throws \OutOfBoundsException When no property for <b>$name</b> exists.
     */
    public function getBooleanProperty($name)
    {
        if (isset($this->properties[$name])) {
            return in_array($this->properties[$name], array('true', 'on', 1));
        }
        throw new \OutOfBoundsException('Property "' . $name . '" does not exist.');
    }

    /**
     * Returns the value of a configured property as an integer or throws an
     * exception when no property with <b>$name</b> exists.
     *
     * @param string $name
     * @return integer
     * @throws \OutOfBoundsException When no property for <b>$name</b> exists.
     */
    public function getIntProperty($name)
    {
        if (isset($this->properties[$name])) {
            return (int) $this->properties[$name];
        }
        throw new \OutOfBoundsException('Property "' . $name . '" does not exist.');
    }


    /**
     * Returns the raw string value of a configured property or throws an
     * exception when no property with <b>$name</b> exists.
     *
     * @param string $name
     * @return string
     * @throws \OutOfBoundsException When no property for <b>$name</b> exists.
     */
    public function getStringProperty($name)
    {
        if (isset($this->properties[$name])) {
            return $this->properties[$name];
        }
        throw new \OutOfBoundsException('Property "' . $name . '" does not exist.');

    }

    /**
     * This method adds a violation to all reports for this violation type and
     * for the given <b>$node</b> instance.
     *
     * @param \PHPMD\AbstractNode $node
     * @param array $args
     * @param mixed $metric
     * @return void
     */
    protected function addViolation(
        AbstractNode $node,
        array $args = array(),
        $metric = null
    ) {
        $search  = array();
        $replace = array();
        foreach ($args as $index => $value) {
            $search[]  = '{' . $index . '}';
            $replace[] = $value;
        }

        $message = str_replace($search, $replace, $this->message);

        $ruleViolation = new RuleViolation($this, $node, $message, $metric);
        $this->report->addRuleViolation($ruleViolation);
    }

    /**
     * This method should implement the violation analysis algorithm of concrete
     * rule implementations. All extending classes must implement this method.
     *
     * @param \PHPMD\AbstractNode $node
     * @return void
     */
    abstract public function apply(AbstractNode $node);
}
