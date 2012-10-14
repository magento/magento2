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
 * Configuration of performance tests
 */
class Magento_Performance_Config
{
    /**
     * @var string
     */
    protected $_applicationBaseDir;

    /**
     * @var string
     */
    protected $_applicationUrlHost;

    /**
     * @var string
     */
    protected $_applicationUrlPath;

    /**
     * @var array
     */
    protected $_adminOptions = array();

    /**
     * @var string
     */
    protected $_reportDir;

    /**
     * @var array
     */
    protected $_installOptions = array();

    /**
     * @var array
     */
    protected $_scenarios = array();

    /**
     * Constructor
     *
     * @param array $configData
     * @param string $testsBaseDir
     * @param string $appBaseDir
     * @throws InvalidArgumentException
     * @throws Magento_Exception
     */
    public function __construct(array $configData, $testsBaseDir, $appBaseDir)
    {
        $this->_validateData($configData);
        if (!is_dir($testsBaseDir)) {
            throw new Magento_Exception("Base directory '$testsBaseDir' does not exist.");
        }
        $this->_reportDir = $testsBaseDir . DIRECTORY_SEPARATOR . $configData['report_dir'];

        $applicationOptions = $configData['application'];
        $this->_applicationBaseDir = $appBaseDir;
        $this->_applicationUrlHost = $applicationOptions['url_host'];
        $this->_applicationUrlPath = $applicationOptions['url_path'];
        $this->_adminOptions = $applicationOptions['admin'];

        if (isset($applicationOptions['installation'])) {
            $installConfig = $applicationOptions['installation'];
            $this->_installOptions = $installConfig['options'];
        }

        $this->_expandScenarios($configData['scenario'], $testsBaseDir);
    }

    /**
     * Expands scenario options and file paths glob to a list of scenarios
     * @param array $scenarios
     * @param string $baseDir
     * @throws InvalidArgumentException
     * @throws Magento_Exception
     */
    protected function _expandScenarios($scenarios, $baseDir)
    {
        if (!isset($scenarios['scenarios'])) {
            return;
        }
        if (!is_array($scenarios['scenarios'])) {
            throw new InvalidArgumentException("'scenario' => 'scenarios' option must be an array");
        }

        $commonScenarioConfig = $this->_composeCommonScenarioConfig();
        foreach ($scenarios['scenarios'] as $scenarioName => $scenarioConfig) {
            // Scenarios without additional settings can be presented as direct values of 'scenario' array
            if (!is_array($scenarioConfig)) {
                $scenarioName = $scenarioConfig;
                $scenarioConfig = array();
            }

            // Scenario file
            $scenarioFile = realpath($baseDir . DIRECTORY_SEPARATOR . $scenarioName);
            if (!file_exists($scenarioFile)) {
                throw new Magento_Exception("Scenario '$scenarioName' doesn't exist in $baseDir");
            }

            // Compose config, using global config
            $scenarioConfig = $this->_getCompleteArray($commonScenarioConfig, $scenarioConfig);
            if (isset($scenarios['common_config'])) {
                $scenarioConfig = $this->_getCompleteArray($scenarioConfig, $scenarios['common_config']);
            }

            // Fixtures
            $scenarioConfig['fixtures'] = $this->_expandScenarioFixtures($scenarioConfig, $baseDir);

            // Store scenario
            $this->_scenarios[$scenarioFile] = $scenarioConfig;
        }
    }

    /**
     * Validate high-level configuration structure
     *
     * @param array $configData
     * @throws Magento_Exception
     */
    protected function _validateData(array $configData)
    {
        // Validate 1st-level options data
        $requiredKeys = array('application', 'scenario', 'report_dir');
        foreach ($requiredKeys as $requiredKeyName) {
            if (empty($configData[$requiredKeyName])) {
                throw new Magento_Exception("Configuration array must define '$requiredKeyName' key.");
            }
        }

        // Validate admin options data
        $requiredAdminKeys = array('frontname', 'username', 'password');
        foreach ($requiredAdminKeys as $requiredKeyName) {
            if (empty($configData['application']['admin'][$requiredKeyName])) {
                throw new Magento_Exception("Admin options array must define '$requiredKeyName' key.");
            }
        }
    }

    /**
     * Compose list of all parameters, that must be provided for all scenarios
     *
     * @return array
     */
    protected function _composeCommonScenarioConfig()
    {
        $adminOptions = $this->getAdminOptions();
        return array(
            'arguments' => array(
                Magento_Performance_Scenario_Arguments::ARG_HOST            => $this->getApplicationUrlHost(),
                Magento_Performance_Scenario_Arguments::ARG_PATH            => $this->getApplicationUrlPath(),
                Magento_Performance_Scenario_Arguments::ARG_ADMIN_FRONTNAME => $adminOptions['frontname'],
                Magento_Performance_Scenario_Arguments::ARG_ADMIN_USERNAME  => $adminOptions['username'],
                Magento_Performance_Scenario_Arguments::ARG_ADMIN_PASSWORD  => $adminOptions['password'],
            ),
            'settings' => array(),
            'fixtures' => array()
        );
    }

    /**
     * Retrieve new array composed for an input array by supplementing missing values
     *
     * @param array $input
     * @param array $supplement
     * @return array
     */
    protected function _getCompleteArray(array $input, array $supplement)
    {
        foreach ($supplement as $key => $sourceVal) {
            if (!empty($input[$key])) {
                $input[$key] += $sourceVal;
            } else {
                $input[$key] = $sourceVal;
            }
        }
        return $input;
    }

    /**
     * Process fixture file names from scenario config and compose array of full file paths to them
     *
     * @param array $scenarioConfig
     * @param string $baseDir
     * @return array
     * @throws InvalidArgumentException|Magento_Exception
     */
    protected function _expandScenarioFixtures(array $scenarioConfig, $baseDir)
    {
        if (!is_array($scenarioConfig['fixtures'])) {
            throw new InvalidArgumentException(
                "Scenario 'fixtures' option must be an array, not a value: '{$scenarioConfig['fixtures']}'"
            );
        }

        $result = array();
        foreach ($scenarioConfig['fixtures'] as $fixtureName) {
            $fixtureFile = $baseDir . DIRECTORY_SEPARATOR . $fixtureName;
            if (!file_exists($fixtureFile)) {
                throw new Magento_Exception("Fixture '$fixtureName' doesn't exist in $baseDir");
            }
            $result[] = $fixtureFile;
        }

        return $result;
    }

    /**
     * Retrieve application base directory
     *
     * @return string
     */
    public function getApplicationBaseDir()
    {
        return $this->_applicationBaseDir;
    }

    /**
     * Retrieve application URL host component
     *
     * @return string
     */
    public function getApplicationUrlHost()
    {
        return $this->_applicationUrlHost;
    }

    /**
     * Retrieve application URL path component
     *
     * @return string
     */
    public function getApplicationUrlPath()
    {
        return $this->_applicationUrlPath;
    }

    /**
     * Retrieve admin options - backend path and admin user credentials
     *
     * @return array
     */
    public function getAdminOptions()
    {
        return $this->_adminOptions;
    }

    /**
     * Retrieve application installation options
     *
     * @return array
     */
    public function getInstallOptions()
    {
        return $this->_installOptions;
    }

    /**
     * Retrieve scenario files
     *
     * @return array
     */
    public function getScenarios()
    {
        return array_keys($this->_scenarios);
    }

    /**
     * Retrieve arguments for a scenario
     *
     * @param string $scenarioFile
     * @return Magento_Performance_Scenario_Arguments|null
     */
    public function getScenarioArguments($scenarioFile)
    {
        if (isset($this->_scenarios[$scenarioFile]['arguments'])) {
            return new Magento_Performance_Scenario_Arguments($this->_scenarios[$scenarioFile]['arguments']);
        }
        return null;
    }

    /**
     * Retrieve settings for a scenario
     *
     * @param string $scenarioFile
     * @return array
     */
    public function getScenarioSettings($scenarioFile)
    {
        if (isset($this->_scenarios[$scenarioFile]['settings'])) {
            return $this->_scenarios[$scenarioFile]['settings'];
        }
        return array();
    }

    /**
     * Retrieve fixtures for a scenario
     *
     * @param string $scenarioFile
     * @return array
     */
    public function getScenarioFixtures($scenarioFile)
    {
        if (isset($this->_scenarios[$scenarioFile]['fixtures'])) {
            return $this->_scenarios[$scenarioFile]['fixtures'];
        }
        return array();
    }

    /**
     * Retrieve reports directory
     *
     * @return string
     */
    public function getReportDir()
    {
        return $this->_reportDir;
    }
}
