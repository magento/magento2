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
     * Complete all Project Setup tasks
     */
    function setup()
    {
        $this->_exec('vendor/bin/robo clone:files');
        $this->_exec('vendor/bin/codecept build');
    }

    /**
     * Duplicate the Example configuration files used to customize the Project for customization
     */
    function cloneFiles()
    {
        $this->_exec('cp -vn .env.example .env');
        $this->_exec('cp -vn codeception.dist.yml codeception.yml');
        $this->_exec('cp -vn tests/functional.suite.dist.yml tests/functional.suite.yml');
    }

    /**
     * Build the Codeception project
     */
    function buildProject()
    {
        $this->cloneFiles();
        $this->_exec('vendor/bin/codecept build');
    }

    /**
     * Generate all Tests
     */
    function generateTests()
    {
        require 'tests/functional/_bootstrap.php';
        \Magento\FunctionalTestingFramework\Util\TestGenerator::getInstance()->createAllCestFiles();
        $this->say("Generate Tests Command Run");
    }

    /**
     * Run all Acceptance tests using the Chrome environment
     */
    function chrome()
    {
        $this->_exec('codecept run functional --env chrome --skip-group skip');
    }

    /**
     * Run all Acceptance tests using the FireFox environment
     */
    function firefox()
    {
        $this->_exec('codecept run functional --env firefox --skip-group skip');
    }

    /**
     * Run all Acceptance tests using the PhantomJS environment
     */
    function phantomjs()
    {
        $this->_exec('codecept run functional --env phantomjs --skip-group skip');
    }

    /**
     * Run all Tests with the specified @group tag, excluding @group 'skip', using the Chrome environment
     */
    function group($args = '')
    {
        $this->taskExec('codecept run functional --verbose --steps --env chrome --skip-group skip --group')->args($args)->run();
    }

    /**
     * Run all Acceptance tests located under the Directory Path provided using the Chrome environment
     */
    function folder($args = '')
    {
        $this->taskExec('codecept run functional --env chrome')->args($args)->run();
    }

    /**
     * Run all Tests marked with the @group tag 'example', using the Chrome environment
     */
    function example()
    {
        $this->_exec('codecept run --env chrome --group example --skip-group skip');
    }

    /**
     * Generate the HTML for the Allure report based on the Test XML output - Allure v1.4.X
     */
    function allure1Generate()
    {
        return $this->_exec('allure generate tests/_output/allure-results/ -o tests/_output/allure-report/');
    }

    /**
     * Generate the HTML for the Allure report based on the Test XML output - Allure v2.3.X
     */
    function allure2Generate()
    {
        return $this->_exec('allure generate tests/_output/allure-results/ --output tests/_output/allure-report/ --clean');
    }

    /**
     * Open the HTML Allure report - Allure v1.4.xX
     */
    function allure1Open()
    {
        $this->_exec('allure report open --report-dir tests/_output/allure-report/');
    }

    /**
     * Open the HTML Allure report - Allure v2.3.X
     */
    function allure2Open()
    {
        $this->_exec('allure open --port 0 tests/_output/allure-report/');
    }

    /**
     * Generate and open the HTML Allure report - Allure v1.4.X
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
     */
    function allure2Report()
    {
        $result1 = $this->allure2Generate();

        if ($result1->wasSuccessful()) {
            $this->allure2Open();
        }
    }
}
