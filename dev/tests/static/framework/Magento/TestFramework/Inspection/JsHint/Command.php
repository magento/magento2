<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/**
 * PHP JsHint shell command
 */
namespace Magento\TestFramework\Inspection\JsHint;

class Command extends \Magento\TestFramework\Inspection\AbstractCommand
{
    /**
     * @var string
     */
    protected $_fileName;

    /**
     * @var string
     */
    protected $_reportFile;

    /**
     * Constructor
     *
     * @param string $fileName js file name
     * @param string $reportFile Destination file to write JsHint report to
     */
    public function __construct($fileName, $reportFile)
    {
        $this->_fileName = $fileName;
        $this->_reportFile = $reportFile;
    }

    /**
     * Method return instant variable fileName
     * @return string
     */
    public function getFileName()
    {
        return $this->_fileName;
    }

    /**
     * Unable to get JsHint version from command line
     * @return string
     */
    protected function _buildVersionShellCmd()
    {
        return null;
    }

    /**
     * Method return HostScript cscript for windows and rhino for linux
     * $isRunCmd specify if method is called by runCmd in linux or by canRun method
     * @return string
     */
    protected function _getHostScript($isRunCmd = false)
    {
        if ($this->_isOsWin()) {
            return 'cscript ';
        } else {
            return $isRunCmd ? 'rhino ' : 'which rhino &> /dev/null';
        }
    }

    /**
     * Overwirte parent method, $whiteList and $blackList are not used in this implementation
     * @param array $whiteList
     * @param array $blackList
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _buildShellCmd($whiteList, $blackList)
    {
        return $this->_getHostScript(
            true
        ) .
            ' ' .
            '"' .
            $this->_getJsHintPath() .
            '" ' .
            '"' .
            $this->getFileName() .
            '" ' .
            $this->_getJsHintOptions();
    }

    /**
     * Overwrite parent method, keep report file, Build and execute the shell command
     *
     * @param array $whiteList Files/directories to be inspected
     * @param array $blackList Files/directories to be excluded from the inspection
     * @return bool
     */
    public function run(array $whiteList, array $blackList = [])
    {
        $shellCmd = $this->_buildShellCmd($whiteList, $blackList);
        $result = $this->_execShellCmd($shellCmd);
        $this->_generateLastRunMessage();
        return $result !== false;
    }

    /**
     * Check if OS is windows
     * @return boolean
     */
    protected function _isOsWin()
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }

    /**
     * Return default JsHintOptions and format it based on OS
     * @return string
     */
    protected function _getJsHintOptions()
    {
        $jsHintOptionsArray = [
            'browser' => 'true',
            'eqnull' => 'true',
            'expr' => 'true',
            'jquery' => 'true',
            'supernew' => 'true',
        ];
        $jsHintOptions = null;
        if ($this->_isOsWin()) {
            foreach ($jsHintOptionsArray as $key => $value) {
                $jsHintOptions .= "/{$key}:{$value} ";
            }
        } else {
            foreach ($jsHintOptionsArray as $key => $value) {
                $jsHintOptions .= "{$key}={$value},";
            }
        }
        return trim(rtrim($jsHintOptions, ","));
    }

    /**
     * Execute a shell command on the current environment and return its output or FALSE on failure
     *
     * @param string $shellCmd
     * @return string|false
     */
    protected function _execShellCmd($shellCmd)
    {
        $retArray = $this->_executeCommand($shellCmd);
        $this->_lastOutput = implode(PHP_EOL, $retArray[0]);
        $this->_lastExitCode = $retArray[1];
        if ($this->_lastExitCode == 0) {
            return $this->_lastOutput;
        }
        if ($this->_isOsWin()) {
            $output = array_slice($retArray[0], 2);
        }
        $output[] = '';
        //empty line to separate each file output
        file_put_contents($this->_reportFile, $this->_lastOutput, FILE_APPEND);
        return false;
    }

    /**
     * Return JsHintPath
     * @return string
     */
    protected function _getJsHintPath()
    {
        return TESTS_JSHINT_PATH;
    }

    /**
     * Check is file exists
     * @param string $fileName
     * @return string
     */
    protected function _fileExists($fileName)
    {
        return is_file($fileName);
    }

    /**
     * Execute command and return command output and system status
     * @param string $cmd
     * @return array
     */
    protected function _executeCommand($cmd)
    {
        exec(trim($cmd), $output, $retVal);
        return [$output, $retVal];
    }

    /**
     * Check if JsHint is runnable
     * @throws \Exception
     * @return boolean
     */
    public function canRun()
    {
        $retArray = $this->_executeCommand($this->_getHostScript());
        if ($retArray[1] != 0) {
            throw new \Exception($this->_getHostScript() . ' does not exist.');
        }
        if (!$this->_fileExists($this->_getJsHintPath())) {
            throw new \Exception($this->_getJsHintPath() . ' does not exist.');
        }
        if (!$this->_fileExists($this->getFileName())) {
            throw new \Exception($this->getFileName() . ' does not exist.');
        }
        return true;
    }
}
