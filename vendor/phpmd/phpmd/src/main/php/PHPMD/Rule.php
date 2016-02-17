<?php
/**
 * This file is part of PHPMD.
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
 * Base interface for a PHPMD rule.
 *
 * @author    Manuel Pichler <mapi@phpmd.org>
 * @copyright 2008-2014 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 * @since     1.1.0
 */
interface Rule
{
    /**
     * The default lowest rule priority.
     */
    const LOWEST_PRIORITY = 5;

    /**
     * Returns the name for this rule instance.
     *
     * @return string
     */
    public function getName();

    /**
     * Sets the name for this rule instance.
     *
     * @param string $name
     * @return void
     */
    public function setName($name);

    /**
     * Returns the version since when this rule is available or <b>null</b>.
     *
     * @return string
     */
    public function getSince();

    /**
     * Sets the version since when this rule is available.
     *
     * @param string $since
     * @return void
     */
    public function setSince($since);

    /**
     * Returns the violation message text for this rule.
     *
     * @return string
     */
    public function getMessage();

    /**
     * Sets the violation message text for this rule.
     *
     * @param string $message
     * @return void
     */
    public function setMessage($message);

    /**
     * Returns an url will external information for this rule.
     *
     * @return string
     */
    public function getExternalInfoUrl();

    /**
     * Sets an url will external information for this rule.
     *
     * @param string $externalInfoUrl
     * @return void
     */
    public function setExternalInfoUrl($externalInfoUrl);

    /**
     * Returns the description text for this rule instance.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Sets the description text for this rule instance.
     *
     * @param string $description
     * @return void
     */
    public function setDescription($description);

    /**
     * Returns a list of examples for this rule.
     *
     * @return array
     */
    public function getExamples();

    /**
     * Adds a code example for this rule.
     *
     * @param string $example
     * @return void
     */
    public function addExample($example);

    /**
     * Returns the priority of this rule.
     *
     * @return integer
     */
    public function getPriority();

    /**
     * Set the priority of this rule.
     *
     * @param integer $priority
     * @return void
     */
    public function setPriority($priority);

    /**
     * Returns the name of the parent rule-set instance.
     *
     * @return string
     */
    public function getRuleSetName();

    /**
     * Sets the name of the parent rule set instance.
     *
     * @param string $ruleSetName
     * @return void
     */
    public function setRuleSetName($ruleSetName);

    /**
     * Returns the violation report for this rule.
     *
     * @return \PHPMD\Report
     */
    public function getReport();

    /**
     * Sets the violation report for this rule.
     *
     * @param \PHPMD\Report $report
     * @return void
     */
    public function setReport(\PHPMD\Report $report);

    /**
     * Adds a configuration property to this rule instance.
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    public function addProperty($name, $value);

    /**
     * Returns the value of a configured property as a boolean or throws an
     * exception when no property with <b>$name</b> exists.
     *
     * @param string $name
     * @return boolean
     * @throws \OutOfBoundsException When no property for <b>$name</b> exists.
     */
    public function getBooleanProperty($name);

    /**
     * Returns the value of a configured property as an integer or throws an
     * exception when no property with <b>$name</b> exists.
     *
     * @param string $name
     * @return integer
     * @throws \OutOfBoundsException When no property for <b>$name</b> exists.
     */
    public function getIntProperty($name);

    /**
     * This method should implement the violation analysis algorithm of concrete
     * rule implementations. All extending classes must implement this method.
     *
     * @param \PHPMD\AbstractNode $node
     * @return void
     */
    public function apply(AbstractNode $node);
}
