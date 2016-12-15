<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway\Debugger;

use Psr\Log\LoggerInterface;
use Exception;

/**
 * Debugger writes infromation about request, response and possible exception to standard system log.
 */
class Log implements DebuggerInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Log constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function success($requestUrl, $requestData, $responseStatus, $responseBody)
    {
        $requestInfo = $this->buildRequestInfo($requestUrl, $requestData);
        $responseInfo = $this->buildResponseInfo($responseStatus, $responseBody);

        $info = $requestInfo
              . $responseInfo;

        $this->writeToLog($info);
    }

    /**
     * {@inheritdoc}
     */
    public function failure($requestUrl, $requestData, Exception $exception)
    {
        $requestInfo = $this->buildRequestInfo($requestUrl, $requestData);
        $exceptionInfo = $this->buildExceptionInfo($exception);

        $info = $requestInfo
              . $exceptionInfo;

        $this->writeToLog($info);
    }

    /**
     * Build string with request URL and body
     *
     * @param string $requestUrl
     * @param string $requestData
     * @return string
     */
    private function buildRequestInfo($requestUrl, $requestData)
    {
        $infoContent = $this->buildInfoSection('URL', $requestUrl)
                     . $this->buildInfoSection('Body', $requestData);

        $info = $this->buildInfoSection('Request', $infoContent);
        return $info;
    }

    /**
     * Build string with response status code and body
     *
     * @param string $responseStatus
     * @param string $responseBody
     * @return string
     */
    private function buildResponseInfo($responseStatus, $responseBody)
    {
        $infoContent = $this->buildInfoSection('Status', $responseStatus)
                     . $this->buildInfoSection('Body', $responseBody);

        $info = $this->buildInfoSection('Response', $infoContent);
        return $info;
    }

    /**
     * Build string with exception information
     *
     * @param Exception $exception
     * @return string
     */
    private function buildExceptionInfo(Exception $exception)
    {
        $infoContent = (string)$exception;
        $info = $this->buildInfoSection('Exception', $infoContent);
        return $info;
    }

    /**
     * Write debug information to log file (var/log/debug.log by default)
     *
     * @param string $info
     * @return void
     */
    private function writeToLog($info)
    {
        $logMessage = $this->buildInfoSection('Signifyd API integartion debug info', $info);
        $this->logger->debug($logMessage);
    }

    /**
     * Build unified debug section string
     *
     * @param string $title
     * @param string $content
     * @return string
     */
    private function buildInfoSection($title, $content)
    {
        $formattedInfo = $title . ":\n"
                       . $this->addIndent($content) . "\n";
        return $formattedInfo;
    }

    /**
     * Add indent to each line in content
     *
     * @param string $content
     * @param string $indent
     * @return string
     */
    private function addIndent($content, $indent = '    ')
    {
        $contentLines = explode("\n", $content);
        $contentLinesWithIndent = array_map(function ($line) use ($indent) {
            return $indent . $line;
        }, $contentLines);
        $content = implode("\n", $contentLinesWithIndent);
        return $content;
    }
}
