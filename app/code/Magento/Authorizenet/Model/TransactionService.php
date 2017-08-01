<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\Simplexml\Element;
use Magento\Framework\Xml\Security;
use Magento\Authorizenet\Model\Authorizenet;
use Magento\Payment\Model\Method\Logger;

/**
 * Class TransactionService
 * @package Magento\Authorizenet\Model
 * @since 2.0.0
 */
class TransactionService
{
    /**
     * Transaction Details gateway url
     */
    const CGI_URL_TD = 'https://apitest.authorize.net/xml/v1/request.api';

    const PAYMENT_UPDATE_STATUS_CODE_SUCCESS = 'Ok';

    const CONNECTION_TIMEOUT = 45;

    /**
     * Stored information about transaction
     *
     * @var array
     * @since 2.0.0
     */
    protected $transactionDetails = [];

    /**
     * @var \Magento\Framework\Xml\Security
     * @since 2.0.0
     */
    protected $xmlSecurityHelper;

    /**
     * @var \Magento\Payment\Model\Method\Logger
     * @since 2.0.0
     */
    protected $logger;

    /**
     * @var \Magento\Framework\HTTP\ZendClientFactory
     * @since 2.0.0
     */
    protected $httpClientFactory;

    /**
     * Fields that should be replaced in debug with '***'
     *
     * @var array
     * @since 2.0.0
     */
    protected $debugReplacePrivateDataKeys = ['merchantAuthentication', 'x_login'];

    /**
     * @param Security $xmlSecurityHelper
     * @param Logger $logger
     * @param ZendClientFactory $httpClientFactory
     * @since 2.0.0
     */
    public function __construct(
        Security $xmlSecurityHelper,
        Logger $logger,
        ZendClientFactory $httpClientFactory
    ) {
        $this->xmlSecurityHelper = $xmlSecurityHelper;
        $this->logger = $logger;
        $this->httpClientFactory = $httpClientFactory;
    }

    /**
     * Get transaction information
     * @param \Magento\Authorizenet\Model\Authorizenet $context
     * @param string $transactionId
     * @return \Magento\Framework\Simplexml\Element
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function getTransactionDetails(Authorizenet $context, $transactionId)
    {
        return isset($this->transactionDetails[$transactionId])
            ? $this->transactionDetails[$transactionId]
            : $this->loadTransactionDetails($context, $transactionId);
    }

    /**
     * Load transaction details
     *
     * @param \Magento\Authorizenet\Model\Authorizenet $context
     * @param string $transactionId
     * @return \Magento\Framework\Simplexml\Element
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    protected function loadTransactionDetails(Authorizenet $context, $transactionId)
    {

        $requestBody = $this->getRequestBody(
            $context->getConfigData('login'),
            $context->getConfigData('trans_key'),
            $transactionId
        );

        /** @var \Magento\Framework\HTTP\ZendClient $client */
        $client = $this->httpClientFactory->create();
        $url = $context->getConfigData('cgi_url_td') ?: self::CGI_URL_TD;
        $client->setUri($url);
        $client->setConfig(['timeout' => self::CONNECTION_TIMEOUT]);
        $client->setHeaders(['Content-Type: text/xml']);
        $client->setMethod(\Zend_Http_Client::POST);
        $client->setRawData($requestBody);

        $debugData = ['url' => $url, 'request' => $this->removePrivateDataFromXml($requestBody)];

        try {
            $responseBody = $client->request()->getBody();
            if (!$this->xmlSecurityHelper->scan($responseBody)) {
                $this->logger->critical('Attempt loading of external XML entities in response from Authorizenet.');
                throw new \Exception();
            }
            $debugData['response'] = $responseBody;
            libxml_use_internal_errors(true);
            $responseXmlDocument = new Element($responseBody);
            libxml_use_internal_errors(false);
        } catch (\Exception $e) {
            throw new LocalizedException(__('Unable to get transaction details. Try again later.'));
        } finally {
            $context->debugData($debugData);
        }

        if (!isset($responseXmlDocument->messages->resultCode)
            || $responseXmlDocument->messages->resultCode != static::PAYMENT_UPDATE_STATUS_CODE_SUCCESS
        ) {
            throw new LocalizedException(__('Unable to get transaction details. Try again later.'));
        }

        $this->transactionDetails[$transactionId] = $responseXmlDocument;
        return $responseXmlDocument;
    }

    /**
     * Create request body to get transaction details
     * @param string $login
     * @param string $transactionKey
     * @param string $transactionId
     * @return string
     * @since 2.0.0
     */
    private function getRequestBody($login, $transactionKey, $transactionId)
    {
        $requestBody = sprintf(
            '<?xml version="1.0" encoding="utf-8"?>' .
            '<getTransactionDetailsRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">' .
            '<merchantAuthentication><name>%s</name><transactionKey>%s</transactionKey></merchantAuthentication>' .
            '<transId>%s</transId>' .
            '</getTransactionDetailsRequest>',
            $login,
            $transactionKey,
            $transactionId
        );
        return $requestBody;
    }

    /**
     * Remove nodes with private data from XML string
     *
     * Uses values from $_debugReplacePrivateDataKeys property
     *
     * @param string $xml
     * @return string
     * @since 2.0.0
     */
    private function removePrivateDataFromXml($xml)
    {
        foreach ($this->debugReplacePrivateDataKeys as $key) {
            $xml = preg_replace(sprintf('~(?<=<%s>).*?(?=</%s>)~', $key, $key), Logger::DEBUG_KEYS_MASK, $xml);
        }
        return $xml;
    }
}
