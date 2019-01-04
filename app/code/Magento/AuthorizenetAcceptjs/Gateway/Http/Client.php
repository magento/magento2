<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Http;

use Magento\AuthorizenetAcceptjs\Gateway\Config;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Model\Method\Logger;

/**
 * A client that can communicate with the Authorize.net API
 */
class Client implements ClientInterface
{
    const API_ENDPOINT_URL = 'https://api.authorize.net/xml/v1/request.api';

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var ZendClientFactory
     */
    private $httpClientFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var PayloadConverter
     */
    private $payloadConverter;

    /**
     * @param Logger $logger
     * @param ZendClientFactory $httpClientFactory
     * @param Config $config
     * @param PayloadConverter $payloadConverter
     */
    public function __construct(
        Logger $logger,
        ZendClientFactory $httpClientFactory,
        Config $config,
        PayloadConverter $payloadConverter
    ) {
        $this->httpClientFactory = $httpClientFactory;
        $this->payloadConverter = $payloadConverter;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Post request to gateway and return response
     *
     * @param array $request
     * @return array
     * @throws LocalizedException
     * @throws RuntimeException
     */
    /**
     * Places request to gateway. Returns result as ENV array
     *
     * @param \Magento\Payment\Gateway\Http\TransferInterface $transferObject
     * @return array
     * @throws \Magento\Payment\Gateway\Http\ClientException
     */
    public function placeRequest(\Magento\Payment\Gateway\Http\TransferInterface $transferObject)
    {
        $request = $transferObject->getBody();
        $log = [
            'request' => $request,
        ];
        $client = $this->httpClientFactory->create();
        $url = $this->config->getApiUrl() ?: self::API_ENDPOINT_URL;

        try {
            $client->setUri($url);
            $client->setConfig(['maxredirects' => 0, 'timeout' => 30]);

            $client->setRawData($this->payloadConverter->convertArrayToXml($request), 'text/xml');
            $client->setMethod(\Zend_Http_Client::POST);

            $responseBody = $client->request()->getBody();
            $log['response'] = $responseBody;
            $response = $this->payloadConverter->convertXmlToArray($responseBody);
        } catch (\Exception $e) {
            throw new ClientException(
                __('Something went wrong in the payment gateway.')
            );
        } finally {
            $this->logger->debug($log);
        }

        return $response;
    }
}
