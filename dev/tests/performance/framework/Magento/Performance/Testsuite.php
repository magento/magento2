<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     performance_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Performance test suite represents set of performance testing scenarios
 */
class Magento_Performance_Testsuite
{
    /**
     * Do not perform scenario warm up
     */
    const SETTING_SKIP_WARM_UP = 'skip_warm_up';

    /**
     * @var Magento_Performance_Config
     */
    protected $_config;

    /**
     * Application instance to apply fixtures to
     *
     * @var Magento_Application
     */
    protected $_application;

    /**
     * @var Magento_Performance_Scenario_HandlerInterface
     */
    protected $_scenarioHandler;

    /**
     * @var array
     */
    protected $_warmUpArguments = array(
        Magento_Performance_Scenario_Arguments::ARG_USERS => 1,
        Magento_Performance_Scenario_Arguments::ARG_LOOPS => 2,
    );

    /**
     * Constructor
     *
     * @param Magento_Performance_Config $config
     * @param Magento_Application $application
     * @param Magento_Performance_Scenario_HandlerInterface $scenarioHandler
     */
    public function __construct(Magento_Performance_Config $config,
        Magento_Application $application, Magento_Performance_Scenario_HandlerInterface $scenarioHandler
    ) {
        $this->_config = $config;
        $this->_application = $application;
        $this->_scenarioHandler = $scenarioHandler;
    }

    /**
     * Run entire test suite of scenarios
     *
     * @throws Magento_Exception
     */
    public function run()
    {
        $scenarios = $this->_getOptimizedScenarioList();

        foreach ($scenarios as $scenarioFile) {
            $scenarioArguments = $this->_config->getScenarioArguments($scenarioFile);
            $scenarioSettings = $this->_config->getScenarioSettings($scenarioFile);
            $scenarioFixtures = $this->_config->getScenarioFixtures($scenarioFile);

            $this->_application->applyFixtures($scenarioFixtures);

            /* warm up cache, if any */
            if (empty($scenarioSettings[self::SETTING_SKIP_WARM_UP])) {
                $warmUpArgs = new Magento_Performance_Scenario_Arguments(
                    $this->_warmUpArguments + (array)$scenarioArguments
                );
                $this->_scenarioHandler->run($scenarioFile, $warmUpArgs);
            }

            /* full run with reports recording */
            $scenarioName = preg_replace('/\..+?$/', '', basename($scenarioFile));
            $reportFile = $this->_config->getReportDir() . DIRECTORY_SEPARATOR . $scenarioName . '.jtl';
            if (!$this->_scenarioHandler->run($scenarioFile, $scenarioArguments, $reportFile)) {
                throw new Magento_Exception("Unable to run scenario '$scenarioFile', format is not supported.");
            }
        }
    }

    /**
     * Compose optimal list of scenarios, so that Magento reinstalls will be reduced among scenario executions
     *
     * @return array
     */
    protected function _getOptimizedScenarioList()
    {
        $optimizer = new Magento_Performance_Testsuite_Optimizer();
        $scenarios = array();
        foreach ($this->_config->getScenarios() as $scenarioFile) {
            $scenarios[$scenarioFile] = $this->_config->getScenarioFixtures($scenarioFile);
        }
        return $optimizer->run($scenarios);
    }
}
