<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Http\Client;

use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\HTTP\ZendClient;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\ConverterInterface;
use Magento\Payment\Model\Method\Logger;

class Zend implements ClientInterface
{
    /**
     * @var ZendClientFactory
     */
    private $clientFactory;

    /**
     * @var ConverterInterface
     */
    private $converter;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param ZendClientFactory $clientFactory
     * @param ConverterInterface $converter
     * @param Logger $logger
     */
    public function __construct(
        ZendClientFactory $clientFactory,
        ConverterInterface $converter,
        Logger $logger
    ) {
        $this->clientFactory = $clientFactory;
        $this->converter = $converter;
        $this->logger = $logger;
    }

    /**
     * {inheritdoc}
     */
    public function placeRequest(\Magento\Payment\Gateway\Http\TransferInterface $transferObject)
    {
        $log = [
            'request' => $transferObject->getBody(),
            'request_uri' => $transferObject->getUri()
        ];
        $result = [];
        /** @var ZendClient $client */
        $client = $this->clientFactory->create();

        $client->setConfig($transferObject->getClientConfig());
        $client->setMethod($transferObject->getMethod());

        switch($transferObject->getMethod()) {
            case \Zend_Http_Client::GET:
                $client->setParameterGet($transferObject->getBody());
                break;
            case \Zend_Http_Client::POST:
                $client->setParameterPost($transferObject->getBody());
                break;
            default:
                throw new \LogicException(sprintf('Unsupported HTTP method %s', $transferObject->getMethod()));
        }

        $client->setHeaders($transferObject->getHeaders());
        $client->setUrlEncodeBody($transferObject->shouldEncode());
        $client->setUri($transferObject->getUri());

        try {
            $response = $client->request();

            $result = $this->converter->convert($response->getBody());
            $log['response'] = $result;
        } catch (\Zend_Http_Client_Exception $e) {
            throw new \Magento\Payment\Gateway\Http\ClientException(__($e->getMessage()));
        } catch (\Magento\Payment\Gateway\Http\ConverterException $e) {
            throw $e;
        } finally {
            $this->logger->debug($log);
        }

        return $result;
    }
}
