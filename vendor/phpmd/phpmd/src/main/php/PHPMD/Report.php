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
 * The report class collects all found violations and further information about
 * a PHPMD run.
 *
 * @author    Manuel Pichler <mapi@phpmd.org>
 * @copyright 2008-2014 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Report
{
    /**
     * List of rule violations detected in the analyzed source code.
     *
     * @var \PHPMD\RuleViolation[]
     */
    private $ruleViolations = array();

    /**
     * The start time for this report.
     *
     * @var float
     */
    private $startTime = 0.0;

    /**
     * The end time for this report.
     *
     * @var float
     */
    private $endTime = 0.0;

    /**
     * Errors that occurred while parsing the source.
     *
     * @var array
     * @since 1.2.1
     */
    private $errors = array();

    /**
     * Adds a rule violation to this report.
     *
     * @param \PHPMD\RuleViolation $violation
     * @return void
     */
    public function addRuleViolation(RuleViolation $violation)
    {
        $fileName = $violation->getFileName();
        if (!isset($this->ruleViolations[$fileName])) {
            $this->ruleViolations[$fileName] = array();
        }

        $beginLine = $violation->getBeginLine();
        if (!isset($this->ruleViolations[$fileName][$beginLine])) {
            $this->ruleViolations[$fileName][$beginLine] = array();
        }

        $this->ruleViolations[$fileName][$beginLine][] = $violation;
    }

    /**
     * Returns <b>true</b> when this report does not contain any errors.
     *
     * @return boolean
     * @since 0.2.5
     */
    public function isEmpty()
    {
        return (count($this->ruleViolations) === 0);
    }

    /**
     * Returns an iterator with all occurred rule violations.
     *
     * @return \Iterator
     */
    public function getRuleViolations()
    {
        // First sort by file name
        ksort($this->ruleViolations);

        $violations = array();
        foreach ($this->ruleViolations as $violationInLine) {
            // Second sort is by line number
            ksort($violationInLine);

            foreach ($violationInLine as $violation) {
                $violations = array_merge($violations, $violation);
            }
        }

        return new \ArrayIterator($violations);
    }

    /**
     * Adds a processing error that occurred while parsing the source.
     *
     * @param \PHPMD\ProcessingError $error
     * @return void
     * @since 1.2.1
     */
    public function addError(ProcessingError $error)
    {
        $this->errors[] = $error;
    }

    /**
     * Returns <b>true</b> when the report contains at least one processing
     * error. Otherwise this method will return <b>false</b>.
     *
     * @return boolean
     * @since 1.2.1
     */
    public function hasErrors()
    {
        return count($this->errors) > 0;
    }

    /**
     * Returns an iterator with all {@link \PHPMD\ProcessingError} that were
     * added to this report.
     *
     * @return \Iterator
     * @since 1.2.1
     */
    public function getErrors()
    {
        return new \ArrayIterator($this->errors);
    }

    /**
     * Starts the time tracking of this report instance.
     *
     * @return void
     */
    public function start()
    {
        $this->startTime = microtime(true) * 1000.0;
    }

    /**
     * Stops the time tracking of this report instance.
     *
     * @return void
     */
    public function end()
    {
        $this->endTime = microtime(true) * 1000.0;
    }

    /**
     * Returns the total time elapsed for the source analysis.
     *
     * @return float
     */
    public function getElapsedTimeInMillis()
    {
        return round($this->endTime - $this->startTime);
    }
}
