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
 * This is the main facade of the PHP PMD application
 *
 * @author    Manuel Pichler <mapi@phpmd.org>
 * @copyright 2008-2014 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class PHPMD
{
    /**
     * The current PHPMD version.
     */
    const VERSION = '@project.version@';

    /**
     * List of valid file extensions for analyzed files.
     *
     * @var array(string)
     */
    private $fileExtensions = array('php', 'php3', 'php4', 'php5', 'inc');

    /**
     * List of exclude directory patterns.
     *
     * @var array(string)
     */
    private $ignorePatterns = array('.git', '.svn', 'CVS', '.bzr', '.hg', 'SCCS');

    /**
     * The input source file or directory.
     *
     * @var string
     */
    private $input;
    
    /**
     * This property will be set to <b>true</b> when an error or a violation
     * was found in the processed source code.
     *
     * @var boolean
     * @since 0.2.5
     */
    private $violations = false;

    /**
     * Additional options for PHPMD or one of it's parser backends.
     *
     * @var array
     * @since 1.2.0
     */
    private $options = array();

    /**
     * This method will return <b>true</b> when the processed source code
     * contains violations.
     *
     * @return boolean
     * @since 0.2.5
     */
    public function hasViolations()
    {
        return $this->violations;
    }

    /**
     * Returns the input source file or directory path.
     *
     * @return string
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * Returns an array with valid php source file extensions.
     *
     * @return array(string)
     * @since 0.2.0
     */
    public function getFileExtensions()
    {
        return $this->fileExtensions;
    }

    /**
     * Sets a list of filename extensions for valid php source code files.
     *
     * @param array(string) $fileExtensions Extensions without leading dot.
     *
     * @return void
     */
    public function setFileExtensions(array $fileExtensions)
    {
        $this->fileExtensions = $fileExtensions;
    }

    /**
     * Returns an array with string patterns that mark a file path as invalid.
     *
     * @return array(string)
     * @since 0.2.0
     */
    public function getIgnorePattern()
    {
        return $this->ignorePatterns;
    }

    /**
     * Sets a list of ignore patterns that is used to exclude directories from
     * the source analysis.
     *
     * @param array(string) $ignorePatterns List of ignore patterns.
     *
     * @return void
     */
    public function setIgnorePattern(array $ignorePatterns)
    {
        $this->ignorePatterns = array_merge(
            $this->ignorePatterns,
            $ignorePatterns
        );
    }

    /**
     * Returns additional options for PHPMD or one of it's parser backends.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Sets additional options for PHPMD or one of it's parser backends.
     *
     * @param array $options Additional backend or PHPMD options.
     * @return void
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * This method will process all files that can be found in the given input
     * path. It will apply rules defined in the comma-separated <b>$ruleSets</b>
     * argument. The result will be passed to all given renderer instances.
     *
     * @param string $inputPath
     * @param string $ruleSets
     * @param \PHPMD\AbstractRenderer[] $renderers
     * @param \PHPMD\RuleSetFactory $ruleSetFactory
     * @return void
     */
    public function processFiles(
        $inputPath,
        $ruleSets,
        array $renderers,
        RuleSetFactory $ruleSetFactory
    ) {

        // Merge parsed excludes
        $this->ignorePatterns = array_merge($this->ignorePatterns, $ruleSetFactory->getIgnorePattern($ruleSets));

        $this->input = $inputPath;

        $report = new Report();

        $factory = new ParserFactory();
        $parser  = $factory->create($this);

        foreach ($ruleSetFactory->createRuleSets($ruleSets) as $ruleSet) {
            $parser->addRuleSet($ruleSet);
        }

        $report->start();
        $parser->parse($report);
        $report->end();

        foreach ($renderers as $renderer) {
            $renderer->start();
        }
        
        foreach ($renderers as $renderer) {
            $renderer->renderReport($report);
        }

        foreach ($renderers as $renderer) {
            $renderer->end();
        }

        $this->violations = !$report->isEmpty();
    }
}
