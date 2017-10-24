<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** This is project's console commands configuration for Robo task runner.
 *
 * @codingStandardsIgnoreFile
 * @see http://robo.li/
 */
class RoboFile extends \Robo\Tasks
{
    use Robo\Task\Base\loadShortcuts;

    /**
     * Duplicate the Example configuration files used to customize the Project for customization
     *
     * @return void
     */
    function cloneFiles()
    {
        $this->_exec('cp -vn .env.example .env');
        $this->_exec('cp -vn codeception.dist.yml codeception.yml');
        $this->_exec('cp -vn tests/functional.suite.dist.yml tests/functional.suite.yml');
    }

    /**
     * Clone the Example configuration files
     * Build the Codeception project
     *
     * @return void
     */
    function buildProject()
    {
        $this->cloneFiles();
        $this->_exec('./vendor/bin/codecept build');
    }

    /**
     * Generate all Tests command.
     *
     * @param string[] $opts
     * @return void
     */
    function generateTests($opts = ['config' => null, 'env' => 'chrome'])
    {
        require 'tests'. DIRECTORY_SEPARATOR . 'functional' . DIRECTORY_SEPARATOR . '_bootstrap.php';
        \Magento\FunctionalTestingFramework\Util\TestGenerator::getInstance()->createAllCestFiles($opts['config'], $opts['env']);
        $this->say("Generate Tests Command Run");
    }

    /**
     * Generate a suite based on name(s) passed in as args
     *
     * @param string[] args
     * @return void
     * @throws Exception
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
     * Run all Functional tests using the Chrome environment
     *
     * @return void
     */
    function chrome()
    {
        $this->_exec('./vendor/bin/codecept run functional --env chrome --skip-group skip');
    }

    /**
     * Run all Functional tests using the FireFox environment
     *
     * @return void
     */
    function firefox()
    {
        $this->_exec('./vendor/bin/codecept run functional --env firefox --skip-group skip');
    }

    /**
     * Run all Functional tests using the PhantomJS environment
     *
     * @return void
     */
    function phantomjs()
    {
        $this->_exec('./vendor/bin/codecept run functional --env phantomjs --skip-group skip');
    }

    /**
     * Run all Functional tests using the Chrome Headless environment
     *
     * @return void
     */
    function headless()
    {
        $this->_exec('./vendor/bin/codecept run functional --env headless --skip-group skip');
    }

    /**
     * Run all Tests with the specified @group tag, excluding @group 'skip', using the Chrome environment
     *
     * @param string $args
     * @return void
     */
    function group($args = '')
    {
        $this->taskExec('./vendor/bin/codecept run functional --verbose --steps --env chrome --skip-group skip --group')->args($args)->run();
    }

    /**
     * Run all Functional tests located under the Directory Path provided using the Chrome environment
     *
     * @param string $args
     * @return void
     */
    function folder($args = '')
    {
        $this->taskExec('./vendor/bin/codecept run functional --env chrome')->args($args)->run();
    }

    /**
     * Run all Tests marked with the @group tag 'example', using the Chrome environment
     *
     * @return void
     */
    function example()
    {
        $this->_exec('./vendor/bin/codecept run --env chrome --group example --skip-group skip');
    }

    /**
     * Generate the HTML for the Allure report based on the Test XML output - Allure v1.4.X
     *
     * @return void
     */
    function allure1Generate()
    {
        return $this->_exec('allure generate tests/_output/allure-results/ -o tests/_output/allure-report/');
    }

    /**
     * Generate the HTML for the Allure report based on the Test XML output - Allure v2.3.X
     *
     * @return void
     */
    function allure2Generate()
    {
        return $this->_exec('allure generate tests/_output/allure-results/ --output tests/_output/allure-report/ --clean');
    }

    /**
     * Open the HTML Allure report - Allure v1.4.xX
     *
     * @return void
     */
    function allure1Open()
    {
        $this->_exec('allure report open --report-dir tests/_output/allure-report/');
    }

    /**
     * Open the HTML Allure report - Allure v2.3.X
     *
     * @return void
     */
    function allure2Open()
    {
        $this->_exec('allure open --port 0 tests/_output/allure-report/');
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
     * Run the Pre-Install Check Script
     *
     * @return void
     */
    function preInstall()
    {
        $this->_exec('php pre-install.php');
    }
}
