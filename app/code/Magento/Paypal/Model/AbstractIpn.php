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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Paypal\Model;

use \Magento\Paypal\UnavailableException;

class AbstractIpn
{
    /**
     * Default log filename
     */
    const DEFAULT_LOG_FILE = 'paypal_unknown_ipn.log';

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
    protected $_debugData = array();

    /**
     * @var \Magento\Paypal\Model\ConfigFactory
     */
    protected $_configFactory;

    /**
     * @var \Magento\Framework\HTTP\Adapter\CurlFactory
     */
    protected $_curlFactory;

    /**
     * @param \Magento\Paypal\Model\ConfigFactory $configFactory
     * @param \Magento\Framework\Logger\AdapterFactory $logAdapterFactory
     * @param \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Paypal\Model\ConfigFactory $configFactory,
        \Magento\Framework\Logger\AdapterFactory $logAdapterFactory,
        \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory,
        array $data = array()
    ) {
        $this->_configFactory = $configFactory;
        $this->_logAdapterFactory = $logAdapterFactory;
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
        return isset($this->_ipnRequest[$key]) ? $this->_ipnRequest[$key] : null;
    }

    /**
     * Post back to PayPal to check whether this request is a valid one
     *
     * @return void
     * @throws \Exception
     */
    protected function _postBack()
    {
        $httpAdapter = $this->_curlFactory->create();
        $postbackQuery = http_build_query($this->getRequestData()) . '&cmd=_notify-validate';
        $postbackUrl = $this->_config->getPaypalUrl();
        $this->_addDebugData('postback_to', $postbackUrl);

        $httpAdapter->setConfig(array('verifypeer' => $this->_config->getConfigValue('verifyPeer')));
        $httpAdapter->write(\Zend_Http_Client::POST, $postbackUrl, '1.1', array('Connection: close'), $postbackQuery);
        try {
            $postbackResult = $httpAdapter->read();
        } catch (\Exception $e) {
            $this->_addDebugData('http_error', array('error' => $e->getMessage(), 'code' => $e->getCode()));
            throw $e;
        }

        /*
         * Handle errors on PayPal side.
         */
        $responseCode = \Zend_Http_Response::extractCode($postbackResult);
        if (empty($postbackResult) || in_array($responseCode, array('500', '502', '503'))) {
            if (empty($postbackResult)) {
                $reason = 'Empty response.';
            } else {
                $reason = 'Response code: ' . $responseCode . '.';
            }
            $this->_debugData['exception'] = 'PayPal IPN postback failure. ' . $reason;
            throw new UnavailableException($reason);
        }

        $response = preg_split('/^\r?$/m', $postbackResult, 2);
        $response = trim($response[1]);
        if ($response != 'VERIFIED') {
            $this->_addDebugData('postback', $postbackQuery);
            $this->_addDebugData('postback_result', $postbackResult);
            throw new \Exception('PayPal IPN postback failure. See ' . self::DEFAULT_LOG_FILE . ' for details.');
        }
    }

    /**
     * Filter payment status from NVP into paypal/info format
     *
     * @param string $ipnPaymentStatus
     * @return string
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
        if ($this->_config && $this->_config->getConfigValue('debug')) {
            $file = $this->_config
                ->getMethodCode() ? "payment_{$this
                ->_config
                ->getMethodCode()}.log" : self::DEFAULT_LOG_FILE;
            $this->_logAdapterFactory->create(array('fileName' => $file))->log($this->_debugData);
        }
    }

    /**
     * @param string $key
     * @param array|string $value
     * @return $this
     */
    protected function _addDebugData($key, $value)
    {
        $this->_debugData[$key] = $value;
        return $this;
    }
}
