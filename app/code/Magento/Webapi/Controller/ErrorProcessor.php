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
namespace Magento\Webapi\Controller;

class ErrorProcessor
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

    /** @var \Magento\Core\Helper\Data */
    protected $_coreHelper;

    /** @var \Magento\Core\Model\App */
    protected $_app;

    /** @var \Magento\Core\Model\Logger */
    protected $_logger;

    /**
     * Initialize dependencies. Register custom shutdown function.
     *
     * @param \Magento\Core\Helper\Data $helper
     * @param \Magento\Core\Model\App $app
     * @param \Magento\Core\Model\Logger $logger
     */
    public function __construct(
        \Magento\Core\Helper\Data $helper,
        \Magento\Core\Model\App $app,
        \Magento\Core\Model\Logger $logger
    ) {
        $this->_coreHelper = $helper;
        $this->_app = $app;
        $this->_logger = $logger;
        $this->registerShutdownFunction();
    }

    /**
     * Mask actual exception for security reasons in case when it should not be exposed to API clients.
     *
     * Convert any exception into \Magento\Webapi\Exception.
     *
     * @param \Exception $exception
     * @return \Magento\Webapi\Exception
     */
    public function maskException(\Exception $exception)
    {
        /** Log information about actual exception. */
        $reportId = $this->_logException($exception);
        if ($exception instanceof \Magento\Service\Exception) {
            if ($exception instanceof \Magento\Service\ResourceNotFoundException) {
                $httpCode = \Magento\Webapi\Exception::HTTP_NOT_FOUND;
            } elseif ($exception instanceof \Magento\Service\AuthorizationException) {
                $httpCode = \Magento\Webapi\Exception::HTTP_UNAUTHORIZED;
            } else {
                $httpCode = \Magento\Webapi\Exception::HTTP_BAD_REQUEST;
            }
            $maskedException = new \Magento\Webapi\Exception(
                $exception->getMessage(),
                $exception->getCode(),
                $httpCode,
                $exception->getParameters()
            );
        } else if ($exception instanceof \Magento\Webapi\Exception) {
            $maskedException = $exception;
        } else {
            if (!$this->_app->isDeveloperMode()) {
                /** Create exception with masked message. */
                $maskedException = new \Magento\Webapi\Exception(
                    __('Internal Error. Details are available in Magento log file. Report ID: %1', $reportId),
                    0,
                    \Magento\Webapi\Exception::HTTP_INTERNAL_ERROR
                );
            } else {
                $maskedException = new \Magento\Webapi\Exception(
                    $exception->getMessage(),
                    $exception->getCode(),
                    \Magento\Webapi\Exception::HTTP_INTERNAL_ERROR
                );
            }
        }
        return $maskedException;
    }

    /**
     * Process API exception.
     *
     * Create report if not in developer mode and render error to send correct API response.
     *
     * @param \Exception $exception
     * @param int $httpCode
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function renderException(\Exception $exception, $httpCode = self::DEFAULT_ERROR_HTTP_CODE)
    {
        if ($this->_app->isDeveloperMode() || $exception instanceof \Magento\Webapi\Exception) {
            $this->render($exception->getMessage(), $exception->getTraceAsString(), $httpCode);
        } else {
            $reportId = $this->_logException($exception);
            $this->render(
                __('Internal Error. Details are available in Magento log file. Report ID: %1', $reportId),
                'Trace is not available.',
                $httpCode
            );
        }
        die();
    }

    /**
     * Log information about exception to exception log.
     *
     * @param \Exception $exception
     * @return string $reportId
     */
    protected function _logException(\Exception $exception)
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
        if (isset($_SERVER['HTTP_ACCEPT']) && strstr($_SERVER['HTTP_ACCEPT'], 'xml')) {
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
                    . '<message><![CDATA[' . $errorMessage . ']]></message>'
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
     * @return \Magento\Webapi\Controller\ErrorProcessor
     */
    public function registerShutdownFunction()
    {
        register_shutdown_function(array($this, self::DEFAULT_SHUTDOWN_FUNCTION));
        return $this;
    }

    /**
     * Function to catch errors, that has not been caught by the user error dispatcher function.
     */
    public function apiShutdownFunction()
    {
        $fatalErrorFlag = E_ERROR | E_USER_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_RECOVERABLE_ERROR;
        $error = error_get_last();
        if ($error && ($error['type'] & $fatalErrorFlag)) {
            $errorMessage = "Fatal Error: '{$error['message']}' in '{$error['file']}' on line {$error['line']}";
            $reportId = $this->_saveFatalErrorReport($errorMessage);
            if ($this->_app->isDeveloperMode()) {
                $this->render($errorMessage);
            } else {
                $this->render(__('Server internal error. See details in report api/%1', $reportId));
            }
        }
    }

    /**
     * Log information about fatal error.
     *
     * @param string $reportData
     * @return string
     */
    protected function _saveFatalErrorReport($reportData)
    {
        $file = new \Magento\Io\File();
        $reportDir = BP . '/var/report/api';
        $file->checkAndCreateFolder($reportDir, 0777);
        $reportId = abs(intval(microtime(true) * rand(100, 1000)));
        $reportFile = "$reportDir/$reportId";
        $file->write($reportFile, serialize($reportData), 0777);
        return $reportId;
    }
}
