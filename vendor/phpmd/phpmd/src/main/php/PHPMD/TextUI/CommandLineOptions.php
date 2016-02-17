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

namespace PHPMD\TextUI;

use PHPMD\Renderer\HTMLRenderer;
use PHPMD\Renderer\TextRenderer;
use PHPMD\Renderer\XMLRenderer;
use PHPMD\Rule;

/**
 * This is a helper class that collects the specified cli arguments and puts them
 * into accessible properties.
 *
 * @author    Manuel Pichler <mapi@phpmd.org>
 * @copyright 2008-2014 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class CommandLineOptions
{
    /**
     * Error code for invalid input
     */
    const INPUT_ERROR = 23;

    /**
     * The minimum rule priority.
     *
     * @var integer
     */
    protected $minimumPriority = Rule::LOWEST_PRIORITY;

    /**
     * A php source code filename or directory.
     *
     * @var string
     */
    protected $inputPath;

    /**
     * The specified report format.
     *
     * @var string
     */
    protected $reportFormat;

    /**
     * An optional filename for the generated report.
     *
     * @var string
     */
    protected $reportFile;

    /**
     * Additional report files.
     *
     * @var array
     */
    protected $reportFiles = array();

    /**
     * A ruleset filename or a comma-separated string of ruleset filenames.
     *
     * @var string
     */
    protected $ruleSets;

    /**
     * File name of a PHPUnit code coverage report.
     *
     * @var string
     */
    protected $coverageReport;

    /**
     * A string of comma-separated extensions for valid php source code filenames.
     *
     * @var string
     */
    protected $extensions;

    /**
     * A string of comma-separated pattern that is used to exclude directories.
     *
     * @var string
     */
    protected $ignore;

    /**
     * Should the shell show the current phpmd version?
     *
     * @var boolean
     */
    protected $version = false;

    /**
     * Should PHPMD run in strict mode?
     *
     * @var boolean
     * @since 1.2.0
     */
    protected $strict = false;

    /**
     * List of available rule-sets.
     *
     * @var array(string)
     */
    protected $availableRuleSets = array();

    /**
     * Constructs a new command line options instance.
     *
     * @param array $args
     * @param array $availableRuleSets
     * @throws \InvalidArgumentException
     */
    public function __construct(array $args, array $availableRuleSets = array())
    {
        // Remove current file name
        array_shift($args);

        $this->availableRuleSets = $availableRuleSets;

        $arguments = array();
        while (($arg = array_shift($args)) !== null) {
            switch ($arg) {
                case '--minimumpriority':
                    $this->minimumPriority = (int) array_shift($args);
                    break;
                case '--reportfile':
                    $this->reportFile = array_shift($args);
                    break;
                case '--inputfile':
                    array_unshift($arguments, $this->readInputFile(array_shift($args)));
                    break;
                case '--coverage':
                    $this->coverageReport = array_shift($args);
                    break;
                case '--extensions':
                    $this->logDeprecated('extensions', 'suffixes');
                    /* Deprecated: We use the suffixes option now */
                case '--suffixes':
                    $this->extensions = array_shift($args);
                    break;
                case '--ignore':
                    $this->logDeprecated('ignore', 'exclude');
                    /* Deprecated: We use the exclude option now */
                case '--exclude':
                    $this->ignore = array_shift($args);
                    break;
                case '--version':
                    $this->version = true;
                    return;
                case '--strict':
                    $this->strict = true;
                    break;
                case (preg_match('(^\-\-reportfile\-(xml|html|text)$)', $arg, $match) > 0):
                    $this->reportFiles[$match[1]] = array_shift($args);
                    break;
                default:
                    $arguments[] = $arg;
                    break;
            }
        }

        if (count($arguments) < 3) {
            throw new \InvalidArgumentException($this->usage(), self::INPUT_ERROR);
        }

        $this->inputPath    = (string) array_shift($arguments);
        $this->reportFormat = (string) array_shift($arguments);
        $this->ruleSets     = (string) array_shift($arguments);
    }

    /**
     * Returns a php source code filename or directory.
     *
     * @return string
     */
    public function getInputPath()
    {
        return $this->inputPath;
    }

    /**
     * Returns the specified report format.
     *
     * @return string
     */
    public function getReportFormat()
    {
        return $this->reportFormat;
    }

    /**
     * Returns the output filename for a generated report or <b>null</b> when
     * the report should be displayed in STDOUT.
     *
     * @return string
     */
    public function getReportFile()
    {
        return $this->reportFile;
    }

    /**
     * Returns a hash with report files specified for different renderers. The
     * key represents the report format and the value the report file location.
     *
     * @return array
     */
    public function getReportFiles()
    {
        return $this->reportFiles;
    }

    /**
     * Returns a ruleset filename or a comma-separated string of ruleset
     *
     * @return string
     */
    public function getRuleSets()
    {
        return $this->ruleSets;
    }

    /**
     * Returns the minimum rule priority.
     *
     * @return integer
     */
    public function getMinimumPriority()
    {
        return $this->minimumPriority;
    }

    /**
     * Returns the file name of a supplied code coverage report or <b>NULL</b>
     * if the user has not supplied the --coverage option.
     *
     * @return string
     */
    public function getCoverageReport()
    {
        return $this->coverageReport;
    }

    /**
     * Returns a string of comma-separated extensions for valid php source code
     * filenames or <b>null</b> when this argument was not set.
     *
     * @return string
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * Returns  string of comma-separated pattern that is used to exclude
     * directories or <b>null</b> when this argument was not set.
     *
     * @return string
     */
    public function getIgnore()
    {
        return $this->ignore;
    }

    /**
     * Was the <b>--version</b> passed to PHPMD's command line interface?
     *
     * @return boolean
     */
    public function hasVersion()
    {
        return $this->version;
    }

    /**
     * Was the <b>--strict</b> option passed to PHPMD's command line interface?
     *
     * @return boolean
     * @since 1.2.0
     */
    public function hasStrict()
    {
        return $this->strict;
    }

    /**
     * Creates a report renderer instance based on the user's command line
     * argument.
     *
     * Valid renderers are:
     * <ul>
     *   <li>xml</li>
     *   <li>html</li>
     *   <li>text</li>
     * </ul>
     *
     * @param string $reportFormat
     * @return \PHPMD\AbstractRenderer
     * @throws \InvalidArgumentException When the specified renderer does not exist.
     */
    public function createRenderer($reportFormat = null)
    {
        $reportFormat = $reportFormat ?: $this->reportFormat;

        switch ($reportFormat) {
            case 'xml':
                return $this->createXmlRenderer();
            case 'html':
                return $this->createHtmlRenderer();
            case 'text':
                return $this->createTextRenderer();
            default:
                return $this->createCustomRenderer();
        }
    }

    /**
     * @return \PHPMD\Renderer\XMLRenderer
     */
    protected function createXmlRenderer()
    {
        return new XMLRenderer();
    }

    /**
     * @return \PHPMD\Renderer\XMLRenderer
     */
    protected function createTextRenderer()
    {
        return new TextRenderer();
    }

    /**
     * @return \PHPMD\Renderer\HTMLRenderer
     */
    protected function createHtmlRenderer()
    {
        return new HTMLRenderer();
    }

    /**
     * @return \PHPMD\AbstractRenderer
     * @throws \InvalidArgumentException
     */
    protected function createCustomRenderer()
    {
        if ($this->reportFormat !== '') {
            if (class_exists($this->reportFormat)) {
                return new $this->reportFormat();
            }

            // Try to load a custom renderer
            $fileName = strtr($this->reportFormat, '_', '/') . '.php';

            $fileHandle = @fopen($fileName, 'r', true);
            if (is_resource($fileHandle) === false) {
                $message = 'Can\'t find the custom report class: '
                    . $this->reportFormat;
                throw new \InvalidArgumentException($message, self::INPUT_ERROR);
            }
            @fclose($fileHandle);

            include_once $fileName;

            return new $this->reportFormat();
        }
        $message = 'Can\'t create report with format of ' . $this->reportFormat;
        throw new \InvalidArgumentException($message, self::INPUT_ERROR);
    }

    /**
     * Returns usage information for the PHPMD command line interface.
     *
     * @return string
     */
    public function usage()
    {
        return 'Mandatory arguments:' . \PHP_EOL .
               '1) A php source code filename or directory. Can be a comma-' .
               'separated string' . \PHP_EOL .
               '2) A report format' . \PHP_EOL .
               '3) A ruleset filename or a comma-separated string of ruleset' .
               'filenames' . \PHP_EOL . \PHP_EOL .
               'Available formats: xml, text, html.' . \PHP_EOL .
               'Available rulesets: ' . implode(', ', $this->availableRuleSets) . '.' . \PHP_EOL . \PHP_EOL .
               'Optional arguments that may be put after the mandatory arguments:' .
               \PHP_EOL .
               '--minimumpriority: rule priority threshold; rules with lower ' .
               'priority than this will not be used' . \PHP_EOL .
               '--reportfile: send report output to a file; default to STDOUT' .
               \PHP_EOL .
               '--suffixes: comma-separated string of valid source code ' .
               'filename extensions' . \PHP_EOL .
               '--exclude: comma-separated string of patterns that are used to ' .
               'ignore directories' . \PHP_EOL .
                '--strict: also report those nodes with a @SuppressWarnings ' .
               'annotation' . \PHP_EOL;
    }

    /**
     * Logs a deprecated option to the current user interface.
     *
     * @param string $deprecatedName
     * @param string $newName
     * @return void
     */
    protected function logDeprecated($deprecatedName, $newName)
    {
        $message = sprintf(
            'The --%s option is deprecated, please use --%s instead.',
            $deprecatedName,
            $newName
        );

        fwrite(STDERR, $message . \PHP_EOL . \PHP_EOL);
    }

    /**
     * This method takes the given input file, reads the newline separated paths
     * from that file and creates a comma separated string of the file paths. If
     * the given <b>$inputFile</b> not exists, this method will throw an
     * exception.
     *
     * @param string $inputFile Specified input file name.
     * @return string
     * @throws \InvalidArgumentException If the specified input file does not exist.
     * @since 1.1.0
     */
    protected function readInputFile($inputFile)
    {
        if (file_exists($inputFile)) {
            return join(',', array_map('trim', file($inputFile)));
        }
        throw new \InvalidArgumentException("Input file '{$inputFile}' not exists.");
    }
}
