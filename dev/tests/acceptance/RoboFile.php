<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Symfony\Component\Yaml\Yaml;

/** This is project's console commands configuration for Robo task runner.
 *
 * @codingStandardsIgnoreStart
 * @see http://robo.li/
 */
class RoboFile extends \Robo\Tasks
{
    use Robo\Task\Base\loadShortcuts;

    /**
     * Duplicate the Example configuration files for the Project.
     * Build the Codeception project.
     *
     * @return void
     */
    function buildProject()
    {
        passthru($this->getBaseCmd("build:project"));
    }

    /**
     * Generate all Tests in PHP OR Generate set of tests via passing array of tests
     *
     * @param array $tests
     * @param array $opts
     * @return \Robo\Result
     */
    function generateTests(array $tests, $opts = [
        'config' => null,
        'force' => false,
        'nodes' => null,
        'lines' => null,
        'tests' => null
    ])
    {
        $baseCmd = $this->getBaseCmd("generate:tests");

        $mftfArgNames = ['config', 'nodes', 'lines', 'tests'];
        // append arguments to the end of the command
        foreach ($opts as $argName => $argValue) {
            if (in_array($argName, $mftfArgNames) && $argValue !== null) {
                $baseCmd .= " --$argName $argValue";
            }
        }

        // use a separate conditional for the force flag (casting bool to string in php is hard)
        if ($opts['force']) {
            $baseCmd .= ' --force';
        }

        return $this->taskExec($baseCmd)->args($tests)->run();
    }

    /**
     * Generate a suite based on name(s) passed in as args.
     *
     * @param array $args
     * @throws Exception
     * @return \Robo\Result
     */
    function generateSuite(array $args)
    {
        if (empty($args)) {
            throw new Exception("Please provide suite name(s) after generate:suite command");
        }
        $baseCmd = $this->getBaseCmd("generate:suite");
        return $this->taskExec($baseCmd)->args($args)->run();
    }

    /**
     * Run all Tests with the specified @group tag'.
     *
     * @param array $args
     * @return \Robo\Result
     */
    function group(array $args)
    {
        $args = array_merge($args, ['-k']);
        $baseCmd = $this->getBaseCmd("run:group");
        return $this->taskExec($baseCmd)->args($args)->run();
    }

    /**
     * Generate the HTML for the Allure report based on the Test XML output - Allure v1.4.X
     *
     * @return \Robo\Result
     */
    function allure1Generate()
    {
        return $this->_exec('allure generate tests'. DIRECTORY_SEPARATOR .'_output'. DIRECTORY_SEPARATOR .'allure-results'. DIRECTORY_SEPARATOR .' -o tests'. DIRECTORY_SEPARATOR .'_output'. DIRECTORY_SEPARATOR .'allure-report'. DIRECTORY_SEPARATOR .'');
    }

    /**
     * Generate the HTML for the Allure report based on the Test XML output - Allure v2.3.X
     *
     * @return \Robo\Result
     */
    function allure2Generate()
    {
        return $this->_exec('allure generate tests'. DIRECTORY_SEPARATOR .'_output'. DIRECTORY_SEPARATOR .'allure-results'. DIRECTORY_SEPARATOR .' --output tests'. DIRECTORY_SEPARATOR .'_output'. DIRECTORY_SEPARATOR .'allure-report'. DIRECTORY_SEPARATOR .' --clean');
    }

    /**
     * Open the HTML Allure report - Allure v1.4.X
     *
     * @return \Robo\Result
     */
    function allure1Open()
    {
        return $this->_exec('allure report open --report-dir tests'. DIRECTORY_SEPARATOR .'_output'. DIRECTORY_SEPARATOR .'allure-report'. DIRECTORY_SEPARATOR .'');
    }

    /**
     * Open the HTML Allure report - Allure v2.3.X
     *
     * @return \Robo\Result
     */
    function allure2Open()
    {
        return $this->_exec('allure open --port 0 tests'. DIRECTORY_SEPARATOR .'_output'. DIRECTORY_SEPARATOR .'allure-report'. DIRECTORY_SEPARATOR .'');
    }

    /**
     * Generate and open the HTML Allure report - Allure v1.4.X
     *
     * @return \Robo\Result
     */
    function allure1Report()
    {
        $result1 = $this->allure1Generate();

        if ($result1->wasSuccessful()) {
            return $this->allure1Open();
        } else {
            return $result1;
        }
    }

    /**
     * Generate and open the HTML Allure report - Allure v2.3.X
     *
     * @return \Robo\Result
     */
    function allure2Report()
    {
        $result1 = $this->allure2Generate();

        if ($result1->wasSuccessful()) {
            return $this->allure2Open();
        } else {
            return $result1;
        }
    }

    /**
     * Private function for returning the formatted command for the passthru to mftf bin execution.
     *
     * @param string $command
     * @return string
     */
    private function getBaseCmd($command)
    {
        $this->writeln("\033[01;31m Use of robo will be deprecated with next major release, please use <root>/vendor/bin/mftf $command \033[0m");
        chdir(__DIR__);
        return realpath('../../../vendor/bin/mftf') . " $command";
    }
}