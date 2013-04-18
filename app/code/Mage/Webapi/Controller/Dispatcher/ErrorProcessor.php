<?php
/**
 * Helper for errors processing.
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webapi_Controller_Dispatcher_ErrorProcessor
{
    const DEFAULT_SHUTDOWN_FUNCTION = 'apiShutdownFunction';

    const DEFAULT_ERROR_HTTP_CODE = 500;
    const DEFAULT_RESPONSE_CHARSET = 'UTF-8';

    /**#@+
     * Error data representation formats.
     */
    const DATA_FORMAT_JSON = 'json';
    const DATA_FORMAT_XML = 'xml';
    /**#@-*/

    /** @var Mage_Webapi_Helper_Data */
    protected $_apiHelper;

    /** @var Mage_Core_Helper_Data */
    protected $_coreHelper;

    /** @var Mage_Core_Model_App */
    protected $_app;

    /** @var Mage_Core_Model_Logger */
    protected $_logger;

    /**
     * Initialize dependencies. Register custom shutdown function.
     *
     * @param Mage_Core_Model_Factory_Helper $helperFactory
     * @param Mage_Core_Model_App $app
     * @param Mage_Core_Model_Logger $logger
     */
    public function __construct(
        Mage_Core_Model_Factory_Helper $helperFactory,
        Mage_Core_Model_App $app,
        Mage_Core_Model_Logger $logger
    ) {
        $this->_apiHelper = $helperFactory->get('Mage_Webapi_Helper_Data');
        $this->_coreHelper = $helperFactory->get('Mage_Core_Helper_Data');
        $this->_app = $app;
        $this->_logger = $logger;
        $this->registerShutdownFunction();
    }

    /**
     * Mask actual exception for security reasons in case when it should not be exposed to API clients.
     *
     * @param Exception $exception
     * @return Exception
     */
    public function maskException(Exception $exception)
    {
        if (!($exception instanceof Mage_Webapi_Exception) && !$this->_app->isDeveloperMode()) {
            /** Log information about actual exception. */
            $reportId = $this->_logException($exception);
            /** Create exception with masked message. */
            return new Mage_Webapi_Exception(
                $this->_apiHelper
                    ->__('Internal Error. Details are available in Magento log file. Report ID: "%s"', $reportId),
                Mage_Webapi_Exception::HTTP_INTERNAL_ERROR
            );
        } else {
            return $exception;
        }
    }

    /**
     * Process API exception.
     *
     * Create report if not in developer mode and render error to send correct API response.
     *
     * @param Exception $exception
     * @param int $httpCode
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function renderException(Exception $exception, $httpCode = self::DEFAULT_ERROR_HTTP_CODE)
    {
        if ($this->_app->isDeveloperMode() || $exception instanceof Mage_Webapi_Exception) {
            $this->render($exception->getMessage(), $exception->getTraceAsString(), $exception->getCode());
        } else {
            $reportId = $this->_logException($exception);
            $this->render(
                $this->_apiHelper
                    ->__('Internal Error. Details are available in Magento log file. Report ID: "%s"', $reportId),
                'Trace is not available.',
                $httpCode
            );
        }
        // TODO: Move die() call to render() method when it will be covered with functional tests.
        die();
    }

    /**
     * Log information about exception to exception log.
     *
     * @param Exception $exception
     * @return string $reportId
     */
    protected function _logException(Exception $exception)
    {
        $exceptionClass = get_class($exception);
        $reportId = uniqid("webapi-");
        $exceptionForLog = new $exceptionClass(
            /** Trace is added separately by logException. */
            "Report ID: $reportId; Message: {$exception->getMessage()}",
            $exception->getCode()
        );
        $this->_logger->logException($exceptionForLog);
        return $reportId;
    }

    /**
     * Render error according to mime type.
     *
     * @param string $errorMessage
     * @param string $trace
     * @param int $httpCode
     */
    public function render(
        $errorMessage,
        $trace = 'Trace is not available.',
        $httpCode = self::DEFAULT_ERROR_HTTP_CODE
    ) {
        if (strstr($_SERVER['HTTP_ACCEPT'], 'json')) {
            $output = $this->_formatError($errorMessage, $trace, $httpCode, self::DATA_FORMAT_JSON);
            $mimeType = 'application/json';
        } elseif (strstr($_SERVER['HTTP_ACCEPT'], 'xml')) {
            $output = $this->_formatError($errorMessage, $trace, $httpCode, self::DATA_FORMAT_XML);
            $mimeType = 'application/xml';
        } else {
            /** Default format is JSON */
            $output = $this->_formatError($errorMessage, $trace, $httpCode, self::DATA_FORMAT_JSON);
            $mimeType = 'application/json';
        }
        if (!headers_sent()) {
            header('HTTP/1.1 ' . ($httpCode ? $httpCode : self::DEFAULT_ERROR_HTTP_CODE));
            header('Content-Type: ' . $mimeType . '; charset=' . self::DEFAULT_RESPONSE_CHARSET);
        }
        echo $output;
    }

    /**
     * Format error data according to required format.
     *
     * @param string $errorMessage
     * @param string $trace
     * @param string $format
     * @param int $httpCode
     * @return array
     */
    protected function _formatError($errorMessage, $trace, $httpCode, $format)
    {
        $errorData = array();
        $message = array('code' => $httpCode, 'message' => $errorMessage);
        if ($this->_app->isDeveloperMode()) {
            $message['trace'] = $trace;
        }
        $errorData['messages']['error'][] = $message;
        switch ($format) {
            case self::DATA_FORMAT_JSON:
                $errorData = $this->_coreHelper->jsonEncode($errorData);
                break;
            case self::DATA_FORMAT_XML:
                $errorData = '<?xml version="1.0"?>'
                    . '<error>'
                    . '<messages>'
                    . '<error>'
                    . '<data_item>'
                    . '<code>' . $httpCode . '</code>'
                    . '<message>' . $errorMessage . '</message>'
                    . ($this->_app->isDeveloperMode() ? '<trace><![CDATA[' . $trace . ']]></trace>' : '')
                    . '</data_item>'
                    . '</error>'
                    . '</messages>'
                    . '</error>';
                break;
        }
        return $errorData;
    }

    /**
     * Declare web API-specific shutdown function.
     *
     * @return  Mage_Webapi_Controller_Dispatcher_ErrorProcessor
     */
    public function registerShutdownFunction()
    {
        register_shutdown_function(array($this, self::DEFAULT_SHUTDOWN_FUNCTION));
        return $this;
    }

    /**
     * Function to catch errors, that has not been caught by the user error dispatcher function.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    // TODO: Check if this function is useful (it was migrated from M1)
    public function apiShutdownFunction()
    {
        $fatalErrorFlag = E_ERROR | E_USER_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_RECOVERABLE_ERROR;
        $error = error_get_last();
        if ($error && ($error['type'] & $fatalErrorFlag)) {
            $errorMessage = '';
            switch ($error['type']) {
                case E_ERROR:
                    $errorMessage .= "Fatal Error";
                    break;
                case E_PARSE:
                    $errorMessage .= "Parse Error";
                    break;
                case E_CORE_ERROR:
                    $errorMessage .= "Core Error";
                    break;
                case E_COMPILE_ERROR:
                    $errorMessage .= "Compile Error";
                    break;
                case E_USER_ERROR:
                    $errorMessage .= "User Error";
                    break;
                case E_RECOVERABLE_ERROR:
                    $errorMessage .= "Recoverable Error";
                    break;
                default:
                    $errorMessage .= "Unknown error ({$error['type']})";
                    break;
            }
            $errorMessage .= ": {$error['message']}  in {$error['file']} on line {$error['line']}";
            try {
                // call registered error dispatcher
                trigger_error("'$errorMessage'", E_USER_ERROR);
            } catch (Exception $e) {
                $errorMessage = $e->getMessage();
            }
            if (!$this->_app->isDeveloperMode()) {
                $this->_saveFatalErrorReport($errorMessage);
            }
            $this->render($errorMessage);
        }
    }

    /**
     * Log information about fatal error.
     *
     * @param string $reportData
     * @return Mage_Webapi_Controller_Dispatcher_ErrorProcessor
     */
    protected function _saveFatalErrorReport($reportData)
    {
        // TODO refactor method using Varien_Io_File class functions.
        /** Directory for API related reports. */
        /** @see Error_Processor::__construct() */
        $reportDir = BP . DS . 'var' . DS . 'report' . DS . 'api';
        if (!file_exists($reportDir)) {
            @mkdir($reportDir, 0777, true);
        }
        $reportId = abs(intval(microtime(true) * rand(100, 1000)));
        $reportFile = $reportDir . DS . $reportId;
        @file_put_contents($reportFile, serialize($reportData));
        @chmod($reportFile, 0777);
        return $this;
    }
}
