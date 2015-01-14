<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Configuration of performance tests
 */
namespace Magento\TestFramework\Performance;

class Config
{
    /**
     * @var string
     */
    protected $_testsBaseDir;

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
     * @var string
     */
    protected $_reportDir;

    /**
     * @var array
     */
    protected $_installOptions = [];

    /**
     * @var array
     */
    protected $_installOptionsNoValue = [];

    /**
     * @var array
     */
    protected $_scenarios = [];

    /**
     * Constructor
     *
     * @param array $configData
     * @param string $testsBaseDir
     * @param string $appBaseDir
     * @throws \InvalidArgumentException
     * @throws \Magento\Framework\Exception
     */
    public function __construct(array $configData, $testsBaseDir, $appBaseDir)
    {
        $this->_validateData($configData);

        if (!is_dir($testsBaseDir)) {
            throw new \Magento\Framework\Exception("Base directory '{$testsBaseDir}' does not exist.");
        }
        $this->_testsBaseDir = $testsBaseDir;
        $this->_reportDir = $this->_getTestsRelativePath($configData['report_dir']);

        $applicationOptions = $configData['application'];
        $this->_applicationBaseDir = $appBaseDir;
        $this->_applicationUrlHost = $applicationOptions['url_host'];
        $this->_applicationUrlPath = $applicationOptions['url_path'];

        if (isset($applicationOptions['installation']['options'])) {
            $this->_installOptions = $applicationOptions['installation']['options'];
        }

        if (isset($applicationOptions['installation']['options_no_value'])) {
            $this->_installOptionsNoValue = $applicationOptions['installation']['options_no_value'];
        }

        $this->_parseScenarios($configData['scenario']);
    }

    /**
     * Get tests base directory
     *
     * @return string
     */
    public function getTestsBaseDir()
    {
        return $this->_testsBaseDir;
    }

    /**
     * Validate high-level configuration structure
     *
     * @param array $configData
     * @throws \Magento\Framework\Exception
     */
    protected function _validateData(array $configData)
    {
        // Validate 1st-level options data
        $requiredKeys = ['application', 'scenario', 'report_dir'];
        foreach ($requiredKeys as $requiredKeyName) {
            if (empty($configData[$requiredKeyName])) {
                throw new \Magento\Framework\Exception("Configuration array must define '{$requiredKeyName}' key.");
            }
        }

        // Validate admin options data
        $requiredAdminKeys = ['admin_username', 'admin_password', 'backend_frontname'];
        foreach ($requiredAdminKeys as $requiredKeyName) {
            if (empty($configData['application']['installation']['options'][$requiredKeyName])) {
                throw new \Magento\Framework\Exception(
                    "Installation options array must define '{$requiredKeyName}' key."
                );
            }
        }
    }

    /**
     * Compose full file path, as relative to the tests directory
     *
     * @param string $path
     * @return string
     */
    protected function _getTestsRelativePath($path)
    {
        return $this->_testsBaseDir . '/' . $path;
    }

    /**
     * Parse scenario configuration
     *
     * @param array $scenarios
     * @throws \InvalidArgumentException
     */
    protected function _parseScenarios(array $scenarios)
    {
        if (!isset($scenarios['scenarios'])) {
            return;
        }
        if (!is_array($scenarios['scenarios'])) {
            throw new \InvalidArgumentException("'scenario' => 'scenarios' option must be an array");
        }

        $commonConfig = isset($scenarios['common_config']) ? $scenarios['common_config'] : [];
        if (!is_array($commonConfig)) {
            throw new \InvalidArgumentException("Common scenario config must be represented by an array'");
        }

        // Parse scenarios one by one
        foreach ($scenarios['scenarios'] as $scenarioTitle => $scenarioConfigData) {
            $this->_scenarios[] = $this->_parseScenario($scenarioTitle, $scenarioConfigData, $commonConfig);
        }
    }

    /**
     * Parses config data into set of configured values
     *
     * @param string $title
     * @param array $config
     * @param array $commonConfig
     * @return \Magento\TestFramework\Performance\Scenario
     * @throws \InvalidArgumentException
     */
    protected function _parseScenario($title, array $config, array $commonConfig)
    {
        // Title
        if (!strlen($title)) {
            throw new \InvalidArgumentException("Scenario must have a title");
        }

        // General config validation
        if (!is_array($config)) {
            throw new \InvalidArgumentException(
                "Configuration of scenario '{$title}' must be represented by an array"
            );
        }

        // File
        if (!isset($config['file'])) {
            throw new \InvalidArgumentException("File is not defined for scenario '{$title}'");
        }
        $file = realpath($this->_getTestsRelativePath($config['file']));
        if (!file_exists($file)) {
            throw new \InvalidArgumentException("File {$config['file']} doesn't exist for scenario '{$title}'");
        }

        // Validate sub arrays
        $subArrays = $this->_validateScenarioSubArrays($title, $config, $commonConfig);

        return new \Magento\TestFramework\Performance\Scenario(
            $title,
            $file,
            $subArrays['arguments'],
            $subArrays['settings'],
            $subArrays['fixtures']
        );
    }

    /**
     * Validate and process scenario arguments, settings and fixtures
     *
     * @param string $title
     * @param array $config
     * @param array $commonConfig
     * @return array
     * @throws \InvalidArgumentException
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _validateScenarioSubArrays($title, array $config, array $commonConfig)
    {
        foreach (['arguments', 'settings', 'fixtures'] as $configKey) {
            if (isset($config[$configKey]) && !is_array($config[$configKey])) {
                throw new \InvalidArgumentException(
                    "'{$configKey}' for scenario '{$title}' must be represented by an array"
                );
            }
        }

        // Compose arguments, settings and fixtures
        $config = $this->_extendScenarioConfig($config, $commonConfig);

        $arguments = isset($config['arguments']) ? $config['arguments'] : [];
        $arguments = array_merge($arguments, $this->_getFixedScenarioArguments());

        $settings = isset($config['settings']) ? $config['settings'] : [];

        $fixtures = isset($config['fixtures']) ? $config['fixtures'] : [];
        $fixtures = $this->_expandFixtures($fixtures);

        return ['arguments' => $arguments, 'settings' => $settings, 'fixtures' => $fixtures];
    }

    /**
     * Extend scenario config by adding default values from common scenarios config
     *
     * @param array $config
     * @param array $commonConfig
     * @return array
     */
    protected function _extendScenarioConfig(array $config, array $commonConfig)
    {
        foreach ($commonConfig as $key => $commonVal) {
            if (empty($config[$key])) {
                $config[$key] = $commonVal;
            } else {
                if ($key == 'fixtures') {
                    $config[$key] = array_merge($config[$key], $commonVal);
                } else {
                    $config[$key] += $commonVal;
                }
            }
        }
        return $config;
    }

    /**
     * Compose list of scenario arguments, calculated by the framework
     *
     * @return array
     */
    protected function _getFixedScenarioArguments()
    {
        $options = $this->getInstallOptions();
        return [
            \Magento\TestFramework\Performance\Scenario::ARG_HOST => $this->getApplicationUrlHost(),
            \Magento\TestFramework\Performance\Scenario::ARG_PATH => $this->getApplicationUrlPath(),
            \Magento\TestFramework\Performance\Scenario::ARG_BASEDIR => $this->getApplicationBaseDir(),
            \Magento\TestFramework\Performance\Scenario::ARG_BACKEND_FRONTNAME => $options['backend_frontname'],
            \Magento\TestFramework\Performance\Scenario::ARG_ADMIN_USERNAME => $options['admin_username'],
            \Magento\TestFramework\Performance\Scenario::ARG_ADMIN_PASSWORD => $options['admin_password'],
            'jmeter.save.saveservice.output_format' => 'xml',
        ];
    }

    /**
     * Process fixture file names from scenario config and compose array of their full file paths
     *
     * @param array $fixtures
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function _expandFixtures(array $fixtures)
    {
        $result = [];
        foreach ($fixtures as $fixtureName) {
            $fixtureFile = realpath($this->_getTestsRelativePath($fixtureName));
            if (!file_exists($fixtureFile)) {
                throw new \InvalidArgumentException(
                    "Fixture '{$fixtureName}' doesn't exist in {$this->_testsBaseDir}"
                );
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
     * Retrieve application installation options
     *
     * @return array
     */
    public function getInstallOptions()
    {
        return $this->_installOptions;
    }

    /**
     * Retrieve application installation options that have no value
     *
     * @return array
     */
    public function getInstallOptionsNoValue()
    {
        return $this->_installOptionsNoValue;
    }

    /**
     * Retrieve scenario configurations - array of \Magento\TestFramework\Performance\Scenario
     *
     * @return array
     */
    public function getScenarios()
    {
        return $this->_scenarios;
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
