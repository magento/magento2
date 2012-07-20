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
class Magento_Config
{
    /**
     * Default value for configuration of benchmarking executable file path
     */
    const DEFAULT_JMETER_JAR_FILE = 'ApacheJMeter.jar';

    /**
     * @var string
     */
    protected $_applicationUrlHost;

    /**
     * @var string
     */
    protected $_applicationUrlPath;

    /**
     * @var string
     */
    protected $_reportDir;

    /**
     * @var string
     */
    protected $_jMeterPath;

    /**
     * @var array
     */
    protected $_installOptions = array();

    /**
     * @var array
     */
    protected $_fixtureFiles = array();

    /**
     * @var array
     */
    protected $_scenarios = array();

    /**
     * Constructor
     *
     * @param array $configData
     * @param string $baseDir
     * @throws Magento_Exception
     */
    public function __construct(array $configData, $baseDir)
    {
        $this->_validateData($configData);
        if (!is_dir($baseDir)) {
            throw new Magento_Exception("Base directory '$baseDir' does not exist.");
        }
        $baseDir = str_replace('\\', '/', realpath($baseDir));
        $this->_reportDir = $baseDir . '/' . $configData['report_dir'];
        $this->_applicationUrlHost = $configData['application']['url_host'];
        $this->_applicationUrlPath = $configData['application']['url_path'];

        if (isset($configData['application']['installation'])) {
            $installConfig = $configData['application']['installation'];
            $this->_installOptions = $installConfig['options'];
            if (isset($installConfig['fixture_files'])) {
                $this->_fixtureFiles = glob($baseDir . '/' . $installConfig['fixture_files'], GLOB_BRACE);
            }
        }

        if (isset($configData['scenario']['jmeter_jar_file'])) {
            $this->_jMeterPath = $configData['scenario']['jmeter_jar_file'];
        } else {
            $this->_jMeterPath = getenv('jmeter_jar_file') ?: self::DEFAULT_JMETER_JAR_FILE;
        }

        $this->_expandScenarios($configData['scenario'], $baseDir);
    }

    /**
     * Expands scenario options and file paths glob to a list of scenarios
     * @param array $scenarios
     * @param string $baseDir
     * @throws Magento_Exception
     */
    protected function _expandScenarios($scenarios, $baseDir)
    {
        if (isset($scenarios['common_params'])) {
            $scenarioParamsCommon = $scenarios['common_params'];
        } else {
            $scenarioParamsCommon = array();
        }

        $scenarioFilesPattern = $baseDir . '/' . $scenarios['files'];
        $scenarioFiles = glob($scenarioFilesPattern, GLOB_BRACE);
        if (!$scenarioFiles) {
            throw new Magento_Exception("No scenario files match '$scenarioFilesPattern' pattern.");
        }
        foreach ($scenarioFiles as $oneScenarioFile) {
            $oneScenarioFile = str_replace('\\', '/', realpath($oneScenarioFile));
            $oneScenarioName = substr($oneScenarioFile, strlen($baseDir) + 1);
            if (isset($scenarios['scenario_params'][$oneScenarioName])) {
                $oneScenarioParams = $scenarios['scenario_params'][$oneScenarioName];
            } else {
                $oneScenarioParams = array();
            }
            $this->_scenarios[$oneScenarioFile] = array_merge($scenarioParamsCommon, $oneScenarioParams);
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
        $requiredKeys = array('application', 'scenario', 'report_dir');
        foreach ($requiredKeys as $requiredKeyName) {
            if (empty($configData[$requiredKeyName])) {
                throw new Magento_Exception("Configuration array must define '$requiredKeyName' key.");
            }
        }
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
     * Retrieve application installation options
     *
     * @return array
     */
    public function getInstallOptions()
    {
        return $this->_installOptions;
    }

    /**
     * Retrieve scenario files and their parameters as array('<scenario_file>' => '<scenario_params>', ...)
     *
     * @return array
     */
    public function getScenarios()
    {
        return $this->_scenarios;
    }

    /**
     * Retrieve fixture script files
     *
     * @return array
     */
    public function getFixtureFiles()
    {
        return $this->_fixtureFiles;
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

    /**
     * Retrieves path to JMeter java file
     *
     * @return string
     */
    public function getJMeterPath()
    {
        return $this->_jMeterPath;
    }
}
