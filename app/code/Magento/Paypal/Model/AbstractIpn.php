<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Model;

use Exception;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Magento\Framework\Exception\RemoteServiceUnavailableException;
use Magento\Framework\HTTP\Adapter\Curl;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Abstract Ipn class for paypal
 */
class AbstractIpn
{
    /**
     * @var Config
     */
    protected $_config;

    /**
     * IPN request data
     *
     * @var array
     */
    protected $_ipnRequest;

    /**
     * Collected debug information
     *
     * @var array
     */
    protected $_debugData = [];

    /**
     * @var ConfigFactory
     */
    protected $_configFactory;

    /**
     * @var CurlFactory
     */
    protected $_curlFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ConfigFactory $configFactory
     * @param LoggerInterface $logger
     * @param CurlFactory $curlFactory
     * @param array $data
     */
    public function __construct(
        ConfigFactory $configFactory,
        LoggerInterface $logger,
        CurlFactory $curlFactory,
        array $data = []
    ) {
        $this->_configFactory = $configFactory;
        $this->logger = $logger;
        $this->_curlFactory = $curlFactory;
        $this->_ipnRequest = $data;
    }

    /**
     * IPN request data getter
     *
     * @param string $key
     * @return array|string
     */
    public function getRequestData($key = null)
    {
        if (null === $key) {
            return $this->_ipnRequest;
        }
        return $this->_ipnRequest[$key] ?? null;
    }

    /**
     * Post back to PayPal to check whether this request is a valid one
     *
     * @return void
     * @throws RemoteServiceUnavailableException
     * @throws Exception
     */
    protected function _postBack()
    {
        /** @var Curl $httpAdapter */
        $httpAdapter = $this->_curlFactory->create();
        $postbackQuery = http_build_query($this->getRequestData()) . '&cmd=_notify-validate';
        $postbackUrl = $this->_config->getPayPalIpnUrl();
        $this->_addDebugData('postback_to', $postbackUrl);

        $httpAdapter->setOptions(['verifypeer' => $this->_config->getValue('verifyPeer')]);
        $httpAdapter->write(Request::METHOD_POST, $postbackUrl, '1.1', ['Connection: close'], $postbackQuery);
        try {
            $postbackResult = $httpAdapter->read();
        } catch (Exception $e) {
            $this->_addDebugData('http_error', ['error' => $e->getMessage(), 'code' => $e->getCode()]);
            throw $e;
        }

        /*
         * Handle errors on PayPal side.
         */
        $responseCode = $this->extractCodeFromResponse($postbackResult);
        if (empty($postbackResult) || in_array($responseCode, ['500', '502', '503'])) {
            if (empty($postbackResult)) {
                $reason = 'Empty response.';
            } else {
                $reason = 'Response code: ' . $responseCode . '.';
            }
            $this->_debugData['exception'] = 'PayPal IPN postback failure. ' . $reason;
            throw new RemoteServiceUnavailableException(__($reason));
        }

        $response = preg_split('/^\r?$/m', $postbackResult, 2);
        $response = isset($response[1]) ? trim($response[1]) : '';
        if ($response != 'VERIFIED') {
            $this->_addDebugData('postback', $postbackQuery);
            $this->_addDebugData('postback_result', $postbackResult);
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new Exception('PayPal IPN postback failure. See system.log for details.');
        }
    }

    /**
     * Filter payment status from NVP into paypal/info format
     *
     * @param string $ipnPaymentStatus
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _filterPaymentStatus($ipnPaymentStatus)
    {
        switch ($ipnPaymentStatus) {
            case 'Created':
                // break is intentionally omitted
            case 'Completed':
                return Info::PAYMENTSTATUS_COMPLETED;
            case 'Denied':
                return Info::PAYMENTSTATUS_DENIED;
            case 'Expired':
                return Info::PAYMENTSTATUS_EXPIRED;
            case 'Failed':
                return Info::PAYMENTSTATUS_FAILED;
            case 'Pending':
                return Info::PAYMENTSTATUS_PENDING;
            case 'Refunded':
                return Info::PAYMENTSTATUS_REFUNDED;
            case 'Reversed':
                return Info::PAYMENTSTATUS_REVERSED;
            case 'Canceled_Reversal':
                return Info::PAYMENTSTATUS_UNREVERSED;
            case 'Processed':
                return Info::PAYMENTSTATUS_PROCESSED;
            case 'Voided':
                return Info::PAYMENTSTATUS_VOIDED;
            default:
                return '';
        }
        // documented in NVP, but not documented in IPN:
        //Info::PAYMENTSTATUS_NONE
        //Info::PAYMENTSTATUS_INPROGRESS
        //Info::PAYMENTSTATUS_REFUNDEDPART
    }

    /**
     * Log debug data to file
     *
     * @return void
     */
    protected function _debug()
    {
        if ($this->_config && $this->_config->getValue('debug')) {
            $this->logger->debug(var_export($this->_debugData, true));
        }
    }

    /**
     * Adding debug data
     *
     * @param string $key
     * @param array|string $value
     * @return $this
     */
    protected function _addDebugData($key, $value)
    {
        $this->_debugData[$key] = $value;
        return $this;
    }

    /**
     * Extract the response code from a response string
     *
     * @param string $responseString
     *
     * @return false|int
     */
    private function extractCodeFromResponse(string $responseString)
    {
        try {
            $responseCode = Response::fromString($responseString)->getStatusCode();
        } catch (Throwable $e) {
            $responseCode = false;
        }

        return $responseCode;
    }
}
