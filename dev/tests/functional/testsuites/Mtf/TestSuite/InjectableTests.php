<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mtf\TestSuite;

use Mtf\ObjectManager;
use Mtf\ObjectManagerFactory;
use Mtf\TestRunner\Configuration;

/**
 * Class InjectableTests
 *
 */
class InjectableTests extends \PHPUnit_Framework_TestSuite
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_TestSuite
     */
    protected $suite;

    /**
     * @var \PHPUnit_Framework_TestResult
     */
    protected $result;

    /**
     * Run collected tests
     *
     * @param \PHPUnit_Framework_TestResult $result
     * @param bool $filter
     * @param array $groups
     * @param array $excludeGroups
     * @param bool $processIsolation
     *
     * @return \PHPUnit_Framework_TestResult|void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function run(
        \PHPUnit_Framework_TestResult $result = null,
        $filter = false,
        array $groups = [],
        array $excludeGroups = [],
        $processIsolation = false
    ) {
        if ($result === null) {
            $this->result = $this->createResult();
        }
    }

    /**
     * Prepare test suite
     *
     * @return mixed
     */
    public static function suite()
    {
        $suite = new self();
        return $suite->prepareSuite();
    }

    /**
     * Prepare test suite and apply application state
     *
     * @return \Mtf\TestSuite\AppState
     */
    public function prepareSuite()
    {
        $this->init();
        return $this->objectManager->create('Mtf\TestSuite\AppState');
    }

    /**
     * Call the initialization of ObjectManager
     */
    public function init()
    {
        $this->initObjectManager();
    }

    /**
     * Initialize ObjectManager
     */
    private function initObjectManager()
    {
        if (!isset($this->objectManager)) {
            $objectManagerFactory = new ObjectManagerFactory();
            $configurationFileName = isset($_ENV['configuration:Mtf/TestSuite/InjectableTests'])
                ? $_ENV['configuration:Mtf/TestSuite/InjectableTests']
                : 'basic';
            $confFilePath = __DIR__ . '/InjectableTests/' . $configurationFileName . '.xml';
            /** @var \Mtf\TestRunner\Configuration $testRunnerConfiguration */
            $testRunnerConfiguration = $objectManagerFactory->getObjectManager()->get('\Mtf\TestRunner\Configuration');
            $testRunnerConfiguration->load($confFilePath);
            $testRunnerConfiguration->loadEnvConfig();

            $shared = ['Mtf\TestRunner\Configuration' => $testRunnerConfiguration];
            $this->objectManager = $objectManagerFactory->create($shared);
        }
    }
}
