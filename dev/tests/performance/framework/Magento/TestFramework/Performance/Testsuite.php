<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Performance test suite represents set of performance testing scenarios
 */
namespace Magento\TestFramework\Performance;

class Testsuite
{
    /**
     * Do not perform scenario warm up
     */
    const SETTING_SKIP_WARM_UP = 'skip_warm_up';

    /**
     * @var \Magento\TestFramework\Performance\Config
     */
    protected $_config;

    /**
     * Application instance to apply fixtures to
     *
     * @var \Magento\TestFramework\Application
     */
    protected $_application;

    /**
     * @var \Magento\TestFramework\Performance\Scenario\HandlerInterface
     */
    protected $_scenarioHandler;

    /**
     * @var array
     */
    protected $_warmUpArguments = [
        \Magento\TestFramework\Performance\Scenario::ARG_USERS => 1,
        \Magento\TestFramework\Performance\Scenario::ARG_LOOPS => 2,
    ];

    /**
     * @var callable
     */
    protected $_onScenarioRun;

    /**
     * @var callable
     */
    protected $_onScenarioFailure;

    /**
     * List of report files that have been used by scenarios
     *
     * @var array
     */
    protected $_reportFiles = [];

    /**
     * Constructor
     *
     * @param \Magento\TestFramework\Performance\Config $config
     * @param \Magento\TestFramework\Application $application
     * @param \Magento\TestFramework\Performance\Scenario\HandlerInterface $scenarioHandler
     */
    public function __construct(
        \Magento\TestFramework\Performance\Config $config,
        \Magento\TestFramework\Application $application,
        \Magento\TestFramework\Performance\Scenario\HandlerInterface $scenarioHandler
    ) {
        $this->_config = $config;
        $this->_application = $application;
        $this->_scenarioHandler = $scenarioHandler;
    }

    /**
     * Run entire test suite of scenarios
     */
    public function run()
    {
        $this->_reportFiles = [];
        $scenarios = $this->_getOptimizedScenarioList();
        foreach ($scenarios as $scenario) {
            /** @var $scenario \Magento\TestFramework\Performance\Scenario */
            $this->_application->applyFixtures($scenario->getFixtures());

            $this->_notifyScenarioRun($scenario);

            /* warm up cache, if any */
            $settings = $scenario->getSettings();
            if (empty($settings[self::SETTING_SKIP_WARM_UP])) {
                try {
                    $scenarioWarmUp = new \Magento\TestFramework\Performance\Scenario(
                        $scenario->getTitle(),
                        $scenario->getFile(),
                        $this->_warmUpArguments + $scenario->getArguments(),
                        $scenario->getSettings(),
                        $scenario->getFixtures()
                    );
                    $this->_scenarioHandler->run($scenarioWarmUp);
                } catch (\Magento\TestFramework\Performance\Scenario\FailureException $scenarioFailure) {
                    // do not notify about failed warm up
                }
            }

            /* full run with reports recording */
            $reportFile = $this->_getScenarioReportFile($scenario);
            try {
                $this->_scenarioHandler->run($scenario, $reportFile);
            } catch (\Magento\TestFramework\Performance\Scenario\FailureException $scenarioFailure) {
                $this->_notifyScenarioFailure($scenarioFailure);
            }
        }
    }

    /**
     * Returns unique report file for the scenario.
     * Used in order to generate unique report file paths for different scenarios that are represented by same files.
     *
     * @param \Magento\TestFramework\Performance\Scenario $scenario
     * @return string
     */
    protected function _getScenarioReportFile(\Magento\TestFramework\Performance\Scenario $scenario)
    {
        $basePath = $this->_config->getReportDir() . '/' . pathinfo($scenario->getFile(), PATHINFO_FILENAME);
        $iteration = 1;
        do {
            $suffix = $iteration == 1 ? '' : '_' . $iteration;
            $reportFile = $basePath . $suffix . '.jtl';
            $iteration++;
        } while (isset($this->_reportFiles[$reportFile]));

        $this->_reportFiles[$reportFile] = true;
        return $reportFile;
    }

    /**
     * Set callback for scenario run event
     *
     * @param callable $callback
     */
    public function onScenarioRun($callback)
    {
        $this->_validateCallback($callback);
        $this->_onScenarioRun = $callback;
    }

    /**
     * Set callback for scenario failure event
     *
     * @param callable $callback
     */
    public function onScenarioFailure($callback)
    {
        $this->_validateCallback($callback);
        $this->_onScenarioFailure = $callback;
    }

    /**
     * Validate whether a callback refers to a valid function/method that can be invoked
     *
     * @param callable $callback
     * @throws \BadFunctionCallException
     */
    protected function _validateCallback($callback)
    {
        if (!is_callable($callback)) {
            throw new \BadFunctionCallException('Callback is invalid.');
        }
    }

    /**
     * Notify about scenario run event
     *
     * @param \Magento\TestFramework\Performance\Scenario $scenario
     */
    protected function _notifyScenarioRun($scenario)
    {
        if ($this->_onScenarioRun) {
            call_user_func($this->_onScenarioRun, $scenario);
        }
    }

    /**
     * Notify about scenario failure event
     *
     * @param \Magento\TestFramework\Performance\Scenario\FailureException $scenarioFailure
     */
    protected function _notifyScenarioFailure(
        \Magento\TestFramework\Performance\Scenario\FailureException $scenarioFailure
    ) {
        if ($this->_onScenarioFailure) {
            call_user_func($this->_onScenarioFailure, $scenarioFailure);
        }
    }

    /**
     * Compose optimal order of scenarios, so that Magento reinstalls will be reduced among scenario executions
     *
     * @return array
     */
    protected function _getOptimizedScenarioList()
    {
        $optimizer = new \Magento\TestFramework\Performance\Testsuite\Optimizer();
        $scenarios = $this->_config->getScenarios();
        $fixtureSets = [];
        foreach ($scenarios as $scenario) {
            /** @var $scenario \Magento\TestFramework\Performance\Scenario */
            $fixtureSets[] = $scenario->getFixtures();
        }
        $keys = $optimizer->optimizeFixtureSets($fixtureSets);

        $result = [];
        foreach ($keys as $key) {
            $result[] = $scenarios[$key];
        }
        return $result;
    }
}
