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
 * Scenario for performance tests
 */
class Magento_Scenario
{
    /**
     * Common scenario parameters
     */
    const PARAM_HOST  = 'host';
    const PARAM_PATH  = 'path';
    const PARAM_LOOPS = 'loops';
    const PARAM_USERS = 'users';
    const PARAM_ADMIN_USERNAME = 'admin_username';
    const PARAM_ADMIN_PASSWORD = 'admin_password';
    const PARAM_ADMIN_FRONTNAME = 'admin_frontname';

    /**
     * @var Magento_Shell
     */
    protected $_shell;

    /**
     * @var string
     */
    protected $_jMeterJarFile;
    /**
     * @var string
     */
    protected $_reportDir;

    /**
     * Constructor
     *
     * @param Magento_Shell $shell
     * @param string $jMeterJarFile
     * @param string $reportDir
     * @throws Magento_Exception
     */
    public function __construct(Magento_Shell $shell, $jMeterJarFile, $reportDir)
    {
        $this->_shell = $shell;
        $this->_jMeterJarFile = $jMeterJarFile;
        $this->_reportDir = $reportDir;

        $this->_validateScenarioExecutable();
        $this->_ensureReportDirExists();
    }

    /**
     * Validate whether scenario executable is available in the environment
     */
    protected function _validateScenarioExecutable()
    {
        $this->_shell->execute('java -jar %s --version', array($this->_jMeterJarFile));
    }

    /**
     * Create writable reports directory, if it does not exist
     */
    protected function _ensureReportDirExists()
    {
        if (!is_dir($this->_reportDir)) {
            mkdir($this->_reportDir, 0777, true);
        }
    }

    /**
     * Run performance testing scenario and write results to the report file
     *
     * @param string $scenarioFile
     * @param array $scenarioParams
     * @throws Magento_Exception
     */
    public function run($scenarioFile, array $scenarioParams)
    {
        if (!file_exists($scenarioFile)) {
            throw new Magento_Exception("Scenario file '$scenarioFile' does not exist.");
        }
        if (empty($scenarioParams[self::PARAM_HOST]) || empty($scenarioParams[self::PARAM_PATH])) {
            throw new Magento_Exception(sprintf(
                "Scenario parameters '%s' and '%s' must be specified.", self::PARAM_HOST, self::PARAM_PATH
            ));
        }

        // Run before-the-scenario PHP script (if exists)
        $beforeOutput = $this->_runScenarioAdditionalScript($scenarioFile, 'before');

        // Dry run - just to warm-up the system
        $dryScenarioParams = array_merge($scenarioParams, array(self::PARAM_USERS => 1, self::PARAM_LOOPS => 2));
        $this->_runScenario($scenarioFile, $dryScenarioParams);

        // Full run
        $fullScenarioParams = $scenarioParams + array(self::PARAM_USERS => 1, self::PARAM_LOOPS => 1);
        $reportFile = $this->_reportDir . DIRECTORY_SEPARATOR . basename($scenarioFile, '.jmx') . '.jtl';
        $this->_runScenario($scenarioFile, $fullScenarioParams, $reportFile);

        // Run after-the-scenario PHP script (if exists)
        $scenarioExecutions = $dryScenarioParams[self::PARAM_USERS] * $dryScenarioParams[self::PARAM_LOOPS]
            + $fullScenarioParams[self::PARAM_USERS] * $fullScenarioParams[self::PARAM_LOOPS];
        $params = array(
            'beforeOutput' => $beforeOutput,
            'scenarioExecutions' => $scenarioExecutions,
        );
        $this->_runScenarioAdditionalScript($scenarioFile, 'after', $params);
    }

    /**
     * Run performance testing scenario.
     *
     * @param string $scenarioFile
     * @param array $scenarioParams
     * @param string|null $reportFile
     */
    protected function _runScenario($scenarioFile, array $scenarioParams, $reportFile = null)
    {
        list($scenarioCmd, $scenarioCmdArgs) = $this->_buildScenarioCmd($scenarioFile, $scenarioParams, $reportFile);
        $this->_shell->execute($scenarioCmd, $scenarioCmdArgs);
        if ($reportFile) {
            $this->_verifyReport($reportFile);
        }
    }

    /**
     * Build and return scenario execution command and arguments for it
     *
     * @param string $scenarioFile
     * @param array $scenarioParams
     * @param string|null $reportFile
     * @return array
     */
    protected function _buildScenarioCmd($scenarioFile, array $scenarioParams, $reportFile = null)
    {
        $command = 'java -jar %s -n -t %s';
        $arguments = array($this->_jMeterJarFile, $scenarioFile);
        if ($reportFile) {
            $command .= ' -l %s';
            $arguments[] = $reportFile;
        }
        foreach ($scenarioParams as $key => $value) {
            $command .= ' %s';
            $arguments[] = "-J$key=$value";
        }
        return array($command, $arguments);
    }

    /**
     * Verify that report XML structure contains no failures and no errors
     *
     * @param string $reportFile
     * @throws Magento_Exception
     */
    protected function _verifyReport($reportFile)
    {
        if (!file_exists($reportFile)) {
            throw new Magento_Exception("Report file '$reportFile' has not been created.");
        }
        $reportXml = simplexml_load_file($reportFile);

        $failedAssertions = $reportXml->xpath('//assertionResult[failure[text()="true"] or error[text()="true"]]');
        if ($failedAssertions) {
            $failureMessages = array("Scenario has failed.");
            foreach ($failedAssertions as $assertionResult) {
                if (isset($assertionResult->failureMessage)) {
                    $failureMessages[] = (string)$assertionResult->failureMessage;
                }
                if (isset($assertionResult->errorMessage)) {
                    $failureMessages[] = (string)$assertionResult->errorMessage;
                }
            }
            throw new Magento_Exception(implode(PHP_EOL, $failureMessages));
        }
    }

    /**
     * Execute additional before/after scenario PHP script, if it exists
     *
     * @param string $scenarioFile
     * @param string $suffix
     * @param array $params
     * @return null|string
     * @throws Magento_Exception
     */
    protected function _runScenarioAdditionalScript($scenarioFile, $suffix, $params = array())
    {
        // Check existence of additional script
        $pathinfo = pathinfo($scenarioFile);
        $scriptFile = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . "{$pathinfo['filename']}_{$suffix}.php";
        if (!file_exists($scriptFile)) {
            return null;
        }

        // Run script
        $cmd = 'php %s';
        $execParams = array($scriptFile);
        foreach ($params as $key => $value) {
            $cmd .= " --{$key}=%s";
            $execParams[] = $value;
        }
        $output = $this->_shell->execute($cmd, $execParams);

        return $output;
    }
}
