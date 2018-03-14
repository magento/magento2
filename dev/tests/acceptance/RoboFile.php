<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** This is project's console commands configuration for Robo task runner.
 *
 * @codingStandardsIgnoreStart
 * @see http://robo.li/
 */
class RoboFile extends \Robo\Tasks
{
    use Robo\Task\Base\loadShortcuts;

    /**
     * Duplicate the Example configuration files used to customize the Project for customization.
     *
     * @return void
     */
    function cloneFiles()
    {
        $this->_exec('cp -vn .env.example .env');
        $this->_exec('cp -vf codeception.dist.yml codeception.yml');
        $this->_exec('cp -vf tests'. DIRECTORY_SEPARATOR .'functional.suite.dist.yml tests'. DIRECTORY_SEPARATOR .'functional.suite.yml');
    }

    /**
     * Duplicate the Example configuration files for the Project.
     * Build the Codeception project.
     *
     * @return void
     */
    function buildProject()
    {
        $this->cloneFiles();
        $this->_exec('vendor'. DIRECTORY_SEPARATOR .'bin'. DIRECTORY_SEPARATOR .'codecept build');
    }

    /**
     * Generate all Tests in PHP.
     *
     * @param array $opts
     * @return void
     */
    function generateTests($opts = ['config' => null, 'force' => false, 'nodes' => null])
    {
        $GLOBALS['GENERATE_TESTS'] = true;

        if ($opts['force'])
        {
            $GLOBALS['FORCE_PHP_GENERATE'] = true;
        }

        require 'tests'. DIRECTORY_SEPARATOR . 'functional' . DIRECTORY_SEPARATOR . '_bootstrap.php';
        \Magento\FunctionalTestingFramework\Util\TestGenerator::getInstance()->createAllTestFiles($opts['config'], $opts['nodes']);
        $this->say("Generate Tests Command Run");
    }

    /**
     * Generate a suite based on name(s) passed in as args.
     *
     * @param array $args
     * @throws Exception
     * @return void
     */
    function generateSuite(array $args)
    {
        if (empty($args)) {
            throw new Exception("Please provide suite name(s) after generate:suite command");
        }

        require 'tests'. DIRECTORY_SEPARATOR . 'functional' . DIRECTORY_SEPARATOR . '_bootstrap.php';
        $sg = \Magento\FunctionalTestingFramework\Suite\SuiteGenerator::getInstance();

        foreach ($args as $arg) {
            $sg->generateSuite($arg);
        }
    }

    /**
     * Run all Functional tests.
     *
     * @return void
     */
    function functional()
    {
        $this->_exec('.' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'codecept run functional --skip-group skip');
    }

    /**
     * Run all Tests with the specified @group tag, excluding @group 'skip'.
     *
     * @param string $args
     * @return void
     */
    function group($args = '')
    {
        $this->taskExec('.' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'codecept run functional --verbose --steps --skip-group skip --group')->args($args)->run();
    }

    /**
     * Run all Functional tests located under the Directory Path provided.
     *
     * @param string $args
     * @return void
     */
    function folder($args = '')
    {
        $this->taskExec('.' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'codecept run functional')->args($args)->run();
    }

    /**
     * Run all Tests marked with the @group tag 'example'.
     *
     * @return void
     */
    function example()
    {
        $this->_exec('.' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'codecept run --group example --skip-group skip');
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
     * @return void
     */
    function allure1Open()
    {
        $this->_exec('allure report open --report-dir tests'. DIRECTORY_SEPARATOR .'_output'. DIRECTORY_SEPARATOR .'allure-report'. DIRECTORY_SEPARATOR .'');
    }

    /**
     * Open the HTML Allure report - Allure v2.3.X
     *
     * @return void
     */
    function allure2Open()
    {
        $this->_exec('allure open --port 0 tests'. DIRECTORY_SEPARATOR .'_output'. DIRECTORY_SEPARATOR .'allure-report'. DIRECTORY_SEPARATOR .'');
    }

    /**
     * Generate and open the HTML Allure report - Allure v1.4.X
     *
     * @return void
     */
    function allure1Report()
    {
        $result1 = $this->allure1Generate();

        if ($result1->wasSuccessful()) {
            $this->allure1Open();
        }
    }

    /**
     * Generate and open the HTML Allure report - Allure v2.3.X
     *
     * @return void
     */
    function allure2Report()
    {
        $result1 = $this->allure2Generate();

        if ($result1->wasSuccessful()) {
            $this->allure2Open();
        }
    }

    /**
     * Run the Pre-Install system check script.
     *
     * @return void
     */
    function preInstall()
    {
        $this->_exec('php pre-install.php');
    }
}
