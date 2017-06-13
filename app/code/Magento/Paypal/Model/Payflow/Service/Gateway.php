<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Payflow\Service;

use Magento\Framework\DataObject;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\Math\Random;
use Magento\Payment\Model\Method\ConfigInterface;
use Magento\Payment\Model\Method\Logger;
use Magento\Payment\Model\Method\Online\GatewayInterface;

/**
 * Gateway Service
 */
class Gateway implements GatewayInterface
{
    /**
     * @var ZendClientFactory
     */
    protected $httpClientFactory;

    /**
     * @var Random
     */
    protected $mathRandom;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param ZendClientFactory $httpClientFactory
     * @param Random $mathRandom
     * @param Logger $logger
     */
    public function __construct(
        ZendClientFactory $httpClientFactory,
        Random $mathRandom,
        Logger $logger
    ) {
        $this->httpClientFactory = $httpClientFactory;
        $this->mathRandom = $mathRandom;
        $this->logger = $logger;
    }

    /**
     * Post request into gateway
     *
     * @param DataObject $request
     * @param ConfigInterface $config
     *
     * @return DataObject
     * @throws \Zend_Http_Client_Exception
     */
    public function postRequest(DataObject $request, ConfigInterface $config)
    {
        $result = new DataObject();

        $clientConfig = [
            'maxredirects' => 5,
            'timeout' => 30,
            'verifypeer' => $config->getValue('verify_peer')
        ];

        if ($config->getValue('use_proxy')) {
            $clientConfig['proxy'] = $config->getValue('proxy_host')
                . ':'
                . $config->getValue('proxy_port');
            $clientConfig['httpproxytunnel'] = true;
            $clientConfig['proxytype'] = CURLPROXY_HTTP;
        }

        /** @var ZendClient $client */
        $client = $this->httpClientFactory->create();

        $client->setUri(
            (bool)$config->getValue('sandbox_flag')
            ? $config->getValue('transaction_url_test_mode')
            : $config->getValue('transaction_url')
        );
        $client->setConfig($clientConfig);
        $client->setMethod(\Zend_Http_Client::POST);
        $client->setParameterPost($request->getData());
        $client->setHeaders(
            [
                'X-VPS-VIT-CLIENT-CERTIFICATION-ID' => '33baf5893fc2123d8b191d2d011b7fdc',
                'X-VPS-Request-ID' => $this->mathRandom->getUniqueHash(),
                'X-VPS-CLIENT-TIMEOUT' => 45
            ]
        );
        $client->setUrlEncodeBody(false);

        try {
            $response = $client->request();

            $responseArray = [];
            parse_str(strstr($response->getBody(), 'RESULT'), $responseArray);

            $result->setData(array_change_key_case($responseArray, CASE_LOWER));
            $result->setData('result_code', $result->getData('result'));
        } catch (\Zend_Http_Client_Exception $e) {
            $result->addData(
                [
                    'response_code' => -1,
                    'response_reason_code' => $e->getCode(),
                    'response_reason_text' => $e->getMessage()
                ]
            );
            throw $e;
        } finally {
            $this->logger->debug(
                [
                    'request' => $request->getData(),
                    'result' => $result->getData()
                ],
                (array)$config->getValue('getDebugReplacePrivateDataKeys'),
                (bool)$config->getValue('debug')
            );
        }

        return $result;
    }
}
