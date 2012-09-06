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
     * @var array
     */
    protected $_adminOptions = array();

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
     * @throws InvalidArgumentException
     * @throws Magento_Exception
     */
    public function __construct(array $configData, $baseDir)
    {
        $this->_validateData($configData);
        if (!is_dir($baseDir)) {
            throw new Magento_Exception("Base directory '$baseDir' does not exist.");
        }
        $this->_reportDir = $baseDir . DIRECTORY_SEPARATOR . $configData['report_dir'];

        $applicationOptions = $configData['application'];
        $this->_applicationUrlHost = $applicationOptions['url_host'];
        $this->_applicationUrlPath = $applicationOptions['url_path'];
        $this->_adminOptions = $applicationOptions['admin'];

        if (isset($applicationOptions['installation'])) {
            $installConfig = $applicationOptions['installation'];
            $this->_installOptions = $installConfig['options'];
            if (isset($installConfig['fixture_files'])) {
                if (!is_array($installConfig['fixture_files'])) {
                    throw new InvalidArgumentException(
                        "'application' => 'installation' => 'fixture_files' option must be array"
                    );
                }
                $this->_fixtureFiles = array();
                foreach ($installConfig['fixture_files'] as $fixtureName) {
                    $fixtureFile = $baseDir . DIRECTORY_SEPARATOR . $fixtureName;
                    if (!file_exists($fixtureFile)) {
                        throw new Magento_Exception("Fixture '$fixtureName' doesn't exist in $baseDir");
                    }
                    $this->_fixtureFiles[] = $fixtureFile;
                }
            }
        }

        if (!empty($configData['scenario']['jmeter_jar_file'])) {
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
     * @throws InvalidArgumentException
     * @throws Magento_Exception
     */
    protected function _expandScenarios($scenarios, $baseDir)
    {
        if (isset($scenarios['common_params'])) {
            $scenarioParamsCommon = $scenarios['common_params'];
        } else {
            $scenarioParamsCommon = array();
        }

        if (isset($scenarios['files'])) {
            if (!is_array($scenarios['files'])) {
                throw new InvalidArgumentException("'scenarios' => 'files' option must be array");
            }
            foreach ($scenarios['files'] as $scenarioName) {
                $scenarioFile = $baseDir . DIRECTORY_SEPARATOR . $scenarioName;
                if (!file_exists($scenarioFile)) {
                    throw new Magento_Exception("Scenario '$scenarioName' doesn't exist in $baseDir");
                }

                if (isset($scenarios['scenario_params'][$scenarioName])) {
                    $oneScenarioParams = $scenarios['scenario_params'][$scenarioName];
                } else {
                    $oneScenarioParams = array();
                }
                $this->_scenarios[$scenarioFile] = array_merge($scenarioParamsCommon, $oneScenarioParams);
            }
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
