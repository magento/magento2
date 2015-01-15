<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Controller;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;
use Magento\Framework\Exception\AbstractAggregateException;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Webapi\Exception as WebapiException;

/**
 * Helper for errors processing.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ErrorProcessor
{
    const DEFAULT_SHUTDOWN_FUNCTION = 'apiShutdownFunction';

    const DEFAULT_ERROR_HTTP_CODE = 500;

    const DEFAULT_RESPONSE_CHARSET = 'UTF-8';

    const INTERNAL_SERVER_ERROR_MSG = 'Internal Error. Details are available in Magento log file. Report ID: %s';

    /**#@+
     * Error data representation formats.
     */
    const DATA_FORMAT_JSON = 'json';

    const DATA_FORMAT_XML = 'xml';

    /**#@-*/

    /**
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreHelper;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * Filesystem instance
     *
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write
     */
    protected $directoryWrite;

    /**
     * @param \Magento\Core\Helper\Data $helper
     * @param \Magento\Framework\App\State $appState
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        \Magento\Core\Helper\Data $helper,
        \Magento\Framework\App\State $appState,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->_coreHelper = $helper;
        $this->_appState = $appState;
        $this->_logger = $logger;
        $this->_filesystem = $filesystem;
        $this->directoryWrite = $this->_filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->registerShutdownFunction();
    }

    /**
     * Mask actual exception for security reasons in case when it should not be exposed to API clients.
     *
     * Convert any exception into \Magento\Webapi\Exception.
     *
     * @param \Exception $exception Exception to convert to a WebAPI exception
     *
     * @return WebapiException
     */
    public function maskException(\Exception $exception)
    {
        $isDevMode = $this->_appState->getMode() === State::MODE_DEVELOPER;
        $stackTrace = $isDevMode ? $exception->getTraceAsString() : null;

        if ($exception instanceof LocalizedException) {
            // Map HTTP codes for LocalizedExceptions according to exception type
            if ($exception instanceof NoSuchEntityException) {
                $httpCode = WebapiException::HTTP_NOT_FOUND;
            } elseif (($exception instanceof AuthorizationException)
                || ($exception instanceof AuthenticationException)
            ) {
                $httpCode = WebapiException::HTTP_UNAUTHORIZED;
            } else {
                // Input, Expired, InvalidState exceptions will fall to here
                $httpCode = WebapiException::HTTP_BAD_REQUEST;
            }

            if ($exception instanceof AbstractAggregateException) {
                $errors = $exception->getErrors();
            } else {
                $errors = null;
            }

            $maskedException = new WebapiException(
                $exception->getRawMessage(),
                $exception->getCode(),
                $httpCode,
                $exception->getParameters(),
                get_class($exception),
                $errors,
                $stackTrace
            );
        } elseif ($exception instanceof WebapiException) {
            $maskedException = $exception;
        } else {
            $message = $exception->getMessage();
            $code = $exception->getCode();
            //if not in Dev mode, make sure the message and code is masked for unanticipated exceptions
            if (!$isDevMode) {
                /** Log information about actual exception */
                $reportId = $this->_critical($exception);
                $message = sprintf(self::INTERNAL_SERVER_ERROR_MSG, $reportId);
                $code = 0;
            }
            $maskedException = new WebapiException(
                $message,
                $code,
                WebapiException::HTTP_INTERNAL_ERROR,
                [],
                '',
                null,
                $stackTrace
            );
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
     * @return void
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function renderException(\Exception $exception, $httpCode = self::DEFAULT_ERROR_HTTP_CODE)
    {
        if ($this->_appState->getMode() == State::MODE_DEVELOPER || $exception instanceof \Magento\Webapi\Exception) {
            $this->render($exception->getMessage(), $exception->getTraceAsString(), $httpCode);
        } else {
            $reportId = $this->_critical($exception);
            $this->render(
                __('Internal Error. Details are available in Magento log file. Report ID: %1', $reportId),
                'Trace is not available.',
                $httpCode
            );
        }
        exit;
    }

    /**
     * Log information about exception to exception log.
     *
     * @param \Exception $exception
     * @return string $reportId
     */
    protected function _critical(\Exception $exception)
    {
        $exceptionClass = get_class($exception);
        $reportId = uniqid("webapi-");
        $exceptionForLog = new $exceptionClass(
            /** Trace is added separately by critical. */
            "Report ID: {$reportId}; Message: {$exception->getMessage()}",
            $exception->getCode()
        );
        $this->_logger->critical($exceptionForLog);
        return $reportId;
    }

    /**
     * Render error according to mime type.
     *
     * @param string $errorMessage
     * @param string $trace
     * @param int $httpCode
     * @return void
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
     * @param int $httpCode
     * @param string $format
     * @return array|string
     */
    protected function _formatError($errorMessage, $trace, $httpCode, $format)
    {
        $errorData = [];
        $message = ['code' => $httpCode, 'message' => $errorMessage];
        $isDeveloperMode = $this->_appState->getMode() == State::MODE_DEVELOPER;
        if ($isDeveloperMode) {
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
                    . ($isDeveloperMode ? '<trace><![CDATA[' . $trace . ']]></trace>' : '')
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
     * @return $this
     */
    public function registerShutdownFunction()
    {
        register_shutdown_function([$this, self::DEFAULT_SHUTDOWN_FUNCTION]);
        return $this;
    }

    /**
     * Function to catch errors, that has not been caught by the user error dispatcher function.
     *
     * @return void
     */
    public function apiShutdownFunction()
    {
        $fatalErrorFlag = E_ERROR | E_USER_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_RECOVERABLE_ERROR;
        $error = error_get_last();
        if ($error && $error['type'] & $fatalErrorFlag) {
            $errorMessage = "Fatal Error: '{$error['message']}' in '{$error['file']}' on line {$error['line']}";
            $reportId = $this->_saveFatalErrorReport($errorMessage);
            if ($this->_appState->getMode() == State::MODE_DEVELOPER) {
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
        $this->directoryWrite->create('report/api');
        $reportId = abs(intval(microtime(true) * rand(100, 1000)));
        $this->directoryWrite->writeFile('report/api/' . $reportId, serialize($reportData));
        return $reportId;
    }
}
