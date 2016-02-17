<?php
/**
 * This file is part of PDepend.
 *
 * PHP Version 5
 *
 * Copyright (c) 2008-2015, Manuel Pichler <mapi@pdepend.org>.
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
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace PDepend\TextUI;

use PDepend\Application;
use PDepend\Util\ConfigurationInstance;
use PDepend\Util\Log;
use PDepend\Util\Workarounds;

/**
 * Handles the command line stuff and starts the text ui runner.
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Command
{
    /**
     * Marks a cli error exit.
     */
    const CLI_ERROR = 1742;

    /**
     * Marks an input error exit.
     */
    const INPUT_ERROR = 1743;

    /**
     * The recieved cli options
     *
     * @var array(string=>mixed)
     */
    private $options = array();

    /**
     * The directories/files to be analyzed
     *
     * @var string
     */
    private $source;

    /**
     * The used text ui runner.
     *
     * @var \PDepend\TextUI\Runner
     */
    private $runner = null;

    /**
     * @var \PDepend\Application
     */
    private $application;

    /**
     * Performs the main cli process and returns the exit code.
     *
     * @return integer
     */
    public function run()
    {
        $this->application = new Application();

        try {
            if ($this->parseArguments() === false) {
                $this->printHelp();
                return self::CLI_ERROR;
            }
        } catch (\Exception $e) {
            echo $e->getMessage(), PHP_EOL, PHP_EOL;

            $this->printHelp();
            return self::CLI_ERROR;
        }

        if (isset($this->options['--help'])) {
            $this->printHelp();
            return Runner::SUCCESS_EXIT;
        }
        if (isset($this->options['--usage'])) {
            $this->printUsage();
            return Runner::SUCCESS_EXIT;
        }
        if (isset($this->options['--version'])) {
            $this->printVersion();
            return Runner::SUCCESS_EXIT;
        }

        $configurationFile = false;

        if (isset($this->options['--configuration'])) {
            $configurationFile = $this->options['--configuration'];

            if (false === file_exists($configurationFile)) {
                $configurationFile = getcwd() . '/' . $configurationFile;
            }
            if (false === file_exists($configurationFile)) {
                $configurationFile = $this->options['--configuration'];
            }

            unset($this->options['--configuration']);
        } elseif (file_exists(getcwd() . '/pdepend.xml')) {
            $configurationFile = getcwd() . '/pdepend.xml';
        } elseif (file_exists(getcwd() . '/pdepend.xml.dist')) {
            $configurationFile = getcwd() . '/pdepend.xml.dist';
        }

        if ($configurationFile) {
            try {
                $this->application->setConfigurationFile($configurationFile);
            } catch (\Exception $e) {
                echo $e->getMessage(), PHP_EOL, PHP_EOL;

                $this->printHelp();
                return self::CLI_ERROR;
            }
        }

        // Create a new text ui runner
        $this->runner = $this->application->getRunner();

        $this->assignArguments();

        // Get a copy of all options
        $options = $this->options;

        // Get an array with all available log options
        $logOptions = $this->application->getAvailableLoggerOptions();

        // Get an array with all available analyzer options
        $analyzerOptions = $this->application->getAvailableAnalyzerOptions();

        foreach ($options as $option => $value) {
            if (isset($logOptions[$option])) {
                // Reduce recieved option list
                unset($options[$option]);
                // Register logger
                $this->runner->addReportGenerator(substr($option, 2), $value);
            } elseif (isset($analyzerOptions[$option])) {
                // Reduce recieved option list
                unset($options[$option]);

                if (isset($analyzerOptions[$option]['value']) && is_bool($value)) {
                    echo 'Option ', $option, ' requires a value.', PHP_EOL;
                    return self::INPUT_ERROR;
                } elseif ($analyzerOptions[$option]['value'] === 'file'
                    && file_exists($value) === false
                ) {
                    echo 'Specified file ', $option, '=', $value,
                         ' not exists.', PHP_EOL;

                    return self::INPUT_ERROR;
                } elseif ($analyzerOptions[$option]['value'] === '*[,...]') {
                    $value = array_map('trim', explode(',', $value));
                }
                $this->runner->addOption(substr($option, 2), $value);
            }
        }

        if (isset($options['--without-annotations'])) {
            // Disable annotation parsing
            $this->runner->setWithoutAnnotations();
            // Remove option
            unset($options['--without-annotations']);
        }

        if (isset($options['--optimization'])) {
            // This option is deprecated.
            echo 'Option --optimization is ambiguous.', PHP_EOL;
            // Remove option
            unset($options['--optimization']);
        }

        if (isset($options['--notify-me'])) {
            $this->runner->addProcessListener(
                new \PDepend\DbusUI\ResultPrinter()
            );
            unset($options['--notify-me']);
        }

        if (count($options) > 0) {
            $this->printHelp();
            echo "Unknown option '", key($options), "' given.", PHP_EOL;
            return self::CLI_ERROR;
        }

        try {
            // Output current pdepend version and author
            $this->printVersion();
            $this->printWorkarounds();

            $startTime = time();

            $result = $this->runner->run();

            if ($this->runner->hasParseErrors() === true) {
                $errors = $this->runner->getParseErrors();

                printf(
                    '%sThe following error%s occured:%s',
                    PHP_EOL,
                    count($errors) > 1 ? 's' : '',
                    PHP_EOL
                );

                foreach ($errors as $error) {
                    echo $error, PHP_EOL;
                }
                echo PHP_EOL;
            }

            $duration = time() - $startTime;
            $hours = intval($duration / 3600);
            $minutes = intval(($duration - $hours * 3600) / 60);
            $seconds = $duration % 60;
            echo PHP_EOL, 'Time: ', sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
            if (function_exists('memory_get_peak_usage')) {
                $memory = (memory_get_peak_usage(true) / (1024 * 1024));
                printf('; Memory: %4.2fMb', $memory);
            }
            echo PHP_EOL;

            return $result;
        } catch (\RuntimeException $e) {
            echo PHP_EOL, PHP_EOL,
                 'Critical error: ', PHP_EOL,
                 '=============== ', PHP_EOL,
                  $e->getMessage(),  PHP_EOL;

            Log::debug($e->getTraceAsString());

            return $e->getCode();
        }
    }

    /**
     * Parses the cli arguments.
     *
     * @return boolean
     */
    protected function parseArguments()
    {
        if (!isset($_SERVER['argv'])) {
            if (false === (boolean) ini_get('register_argc_argv')) {
                // @codeCoverageIgnoreStart
                echo 'Please enable register_argc_argv in your php.ini.';
            } else {
                // @codeCoverageIgnoreEnd
                echo 'Unknown error, no $argv array available.';
            }
            echo PHP_EOL, PHP_EOL;
            return false;
        }

        $argv = $_SERVER['argv'];

        // Remove the pdepend command line file
        array_shift($argv);

        if (count($argv) === 0) {
            return false;
        }

        // Last argument must be a list of source directories
        if (strpos(end($argv), '--') !== 0) {
            $this->source = explode(',', array_pop($argv));
        }

        for ($i = 0, $c = count($argv); $i < $c; ++$i) {
            // Is it an ini_set option?
            if ($argv[$i] === '-d' && isset($argv[$i + 1])) {
                if (strpos($argv[++$i], '=') === false) {
                    ini_set($argv[$i], 'on');
                } else {
                    list($key, $value) = explode('=', $argv[$i]);

                    ini_set($key, $value);
                }
            } elseif (strpos($argv[$i], '=') === false) {
                $this->options[$argv[$i]] = true;
            } else {
                list($key, $value) = explode('=', $argv[$i]);

                $this->options[$key] = $value;
            }
        }

        return true;
    }

    /**
     * Assign CLI arguments to current runner instance
     *
     * @return bool
     */
    protected function assignArguments()
    {
        if ($this->source) {
            $this->runner->setSourceArguments($this->source);
        }

        // Check for suffix option
        if (isset($this->options['--suffix'])) {
            // Get file extensions
            $extensions = explode(',', $this->options['--suffix']);
            // Set allowed file extensions
            $this->runner->setFileExtensions($extensions);

            unset($this->options['--suffix']);
        }

        // Check for ignore option
        if (isset($this->options['--ignore'])) {
            // Get exclude directories
            $directories = explode(',', $this->options['--ignore']);
            // Set exclude directories
            $this->runner->setExcludeDirectories($directories);

            unset($this->options['--ignore']);
        }

        // Check for exclude namespace option
        if (isset($this->options['--exclude'])) {
            // Get exclude directories
            $namespaces = explode(',', $this->options['--exclude']);
            // Set exclude namespace
            $this->runner->setExcludeNamespaces($namespaces);

            unset($this->options['--exclude']);
        }

        // Check for the bad documentation option
        if (isset($this->options['--bad-documentation'])) {
            echo "Option --bad-documentation is ambiguous.", PHP_EOL;

            unset($this->options['--bad-documentation']);
        }

        $configuration = $this->application->getConfiguration();

        // Store in config registry
        ConfigurationInstance::set($configuration);

        if (isset($this->options['--debug'])) {
            unset($this->options['--debug']);

            Log::setSeverity(Log::DEBUG);
        }
    }

    /**
     * Outputs the current PDepend version.
     *
     * @return void
     */
    protected function printVersion()
    {
        $build = __DIR__ . '/../../../../../build.properties';

        if (file_exists($build)) {
            $data = @parse_ini_file($build);
            $version = $data['project.version'];
        } else {
            $version = '@package_version@';
        }

        echo 'PDepend ', $version, PHP_EOL, PHP_EOL;
    }

    /**
     * If the current PHP installation requires some workarounds or limitations,
     * this method will output a message on STDOUT.
     *
     * @return void
     */
    protected function printWorkarounds()
    {
        $workarounds = new Workarounds();

        if ($workarounds->isNotRequired()) {
            return;
        }

        echo 'Your PHP version requires some workaround:', PHP_EOL;
        foreach ($workarounds->getRequiredWorkarounds() as $workaround) {
            echo '- ', $workaround, PHP_EOL;
        }
        echo PHP_EOL;
    }

    /**
     * Outputs the base usage of PDepend.
     *
     * @return void
     */
    protected function printUsage()
    {
        $this->printVersion();
        echo 'Usage: pdepend [options] [logger] <dir[,dir[,...]]>', PHP_EOL, PHP_EOL;
    }

    /**
     * Outputs the main help of PDepend.
     *
     * @return void
     */
    protected function printHelp()
    {
        $this->printUsage();

        $length = $this->printLogOptions();
        $length = $this->printAnalyzerOptions($length);

        $this->printOption(
            '--configuration=<file>',
            'Optional PDepend configuration file.',
            $length
        );
        echo PHP_EOL;

        $this->printOption(
            '--suffix=<ext[,...]>',
            'List of valid PHP file extensions.',
            $length
        );
        $this->printOption(
            '--ignore=<dir[,...]>',
            'List of exclude directories.',
            $length
        );
        $this->printOption(
            '--exclude=<pkg[,...]>',
            'List of exclude namespaces.',
            $length
        );
        echo PHP_EOL;

        $this->printOption(
            '--without-annotations',
            'Do not parse doc comment annotations.',
            $length
        );
        echo PHP_EOL;

        $this->printOption('--debug', 'Prints debugging information.', $length);
        $this->printOption('--help', 'Print this help text.', $length);
        $this->printOption('--version', 'Print the current version.', $length);

        $this->printDbusOption($length);

        $this->printOption('-d key[=value]', 'Sets a php.ini value.', $length);
        echo PHP_EOL;
    }

    /**
     * Prints all available log options and returns the length of the longest
     * option.
     *
     * @return integer
     */
    protected function printLogOptions()
    {
        $maxLength = 0;
        $options   = array();
        $logOptions = $this->application->getAvailableLoggerOptions();
        foreach ($logOptions as $option => $info) {
            // Build log option identifier
            $identifier = sprintf('%s=<%s>', $option, $info['value']);
            // Store in options array
            $options[$identifier] = $info['message'];

            $length = strlen($identifier);
            if ($length > $maxLength) {
                $maxLength = $length;
            }
        }

        ksort($options);

        $last = null;
        foreach ($options as $option => $message) {
            $current = substr($option, 0, strrpos($option, '-'));
            if ($last !== null && $last !== $current) {
                echo PHP_EOL;
            }
            $last = $current;

            $this->printOption($option, $message, $maxLength);
        }
        echo PHP_EOL;

        return $maxLength;
    }

    /**
     * Prints the analyzer options.
     *
     * @param integer $length Length of the longest option.
     *
     * @return integer
     */
    protected function printAnalyzerOptions($length)
    {
        $options = $this->application->getAvailableAnalyzerOptions();

        if (count($options) === 0) {
            return $length;
        }

        ksort($options);

        foreach ($options as $option => $info) {
            if (isset($info['value'])) {
                $option .= '=<' . $info['value'] . '>';
            } else {
                $option .= '=<value>';
            }

            $this->printOption($option, $info['message'], $length);
        }
        echo PHP_EOL;

        return $length;
    }

    /**
     * Prints a single option.
     *
     * @param string  $option  The option identifier.
     * @param string  $message The option help message.
     * @param integer $length  The length of the longest option.
     *
     * @return void
     */
    private function printOption($option, $message, $length)
    {
        // Ignore the phpunit xml option
        if (0 === strpos($option, '--phpunit-xml=')) {
            return;
        }

        // Calculate the max message length
        $mlength = 77 - $length;

        $option = str_pad($option, $length, ' ', STR_PAD_RIGHT);
        echo '  ', $option, ' ';

        $lines = explode(PHP_EOL, wordwrap($message, $mlength, PHP_EOL));
        echo array_shift($lines);

        while (($line = array_shift($lines)) !== null) {
            echo PHP_EOL, str_repeat(' ', $length + 3), $line;
        }
        echo PHP_EOL;
    }

    /**
     * Optionally outputs the dbus option when the required extension
     * is loaded.
     *
     * @param integer $length Padding length for the option.
     *
     * @return void
     */
    private function printDbusOption($length)
    {
        if (extension_loaded("dbus") === false) {
            return;
        }

        $option  = '--notify-me';
        $message = 'Show a notification after analysis.';

        $this->printOption($option, $message, $length);
    }

    /**
     * Main method that starts the command line runner.
     *
     * @return integer The exit code.
     */
    public static function main()
    {
        $command = new Command();
        return $command->run();
    }
}
